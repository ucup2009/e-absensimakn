<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AttendanceReportService;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request, AttendanceReportService $reports): View
    {
        $user = $request->user();
        $filters = $this->filters($request);

        $query = $reports->query($filters, $user);
        $attendances = $query->paginate(15)->withQueryString();
        $summary = $reports->summary($filters, $user);

        $classes = $user->isAdmin()
            ? SchoolClass::orderBy('name')->get()
            : SchoolClass::whereKey($user->homeroomClass?->id)->get();

        $students = Student::query()
            ->with('schoolClass')
            ->where('is_active', true)
            ->when(! $user->isAdmin(), fn ($q) => $q->where('class_id', $user->homeroomClass?->id ?? 0))
            ->orderBy('name')
            ->get();

        return view('attendances.index', [
            'attendances' => $attendances,
            'summary' => $summary,
            'filters' => $filters,
            'classes' => $classes,
            'students' => $students,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function store(Request $request, AttendanceService $attendanceService): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alpa'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::with('schoolClass')->findOrFail($data['student_id']);

        if (! $user->isAdmin() && $student->class_id !== $user->homeroomClass?->id) {
            abort(403);
        }

        $attendanceService->manualStore($student, $data);
        AuditLog::record('attendance.manual', 'Input presensi manual untuk '.$student->name.' pada '.$data['date']);

        return back()->with('success', 'Presensi '.$student->name.' berhasil disimpan.');
    }

    public function exportPdf(Request $request, AttendanceReportService $reports)
    {
        $user = $request->user();

        return $reports->pdf($this->filters($request), $user);
    }

    public function exportExcel(Request $request, AttendanceReportService $reports): StreamedResponse
    {
        $user = $request->user();

        return $reports->excel($this->filters($request), $user);
    }

    protected function filters(Request $request): array
    {
        return [
            'from' => $request->input('from', Carbon::today()->toDateString()),
            'to' => $request->input('to', Carbon::today()->toDateString()),
            'class_id' => $request->input('class_id'),
            'status' => $request->input('status'),
            'q' => $request->input('q'),
        ];
    }
}
