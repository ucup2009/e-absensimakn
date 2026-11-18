<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        $recent = Attendance::query()
            ->with(['student.schoolClass'])
            ->whereDate('date', $today)
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $stats = [
            'total' => Attendance::whereDate('date', $today)->count(),
            'hadir' => Attendance::whereDate('date', $today)->where('status', Attendance::STATUS_HADIR)->count(),
            'terlambat' => Attendance::whereDate('date', $today)->where('status', Attendance::STATUS_TERLAMBAT)->count(),
            'pulang' => Attendance::whereDate('date', $today)->whereNotNull('time_out')->count(),
        ];

        return view('scan.index', [
            'recent' => $recent,
            'stats' => $stats,
            'ontimeLimit' => Setting::get('check_in_limit', '07:15'),
            'checkInStart' => Setting::get('check_in_start', '06:30'),
            'checkOutTime' => Setting::get('check_out_time', '15:30'),
            'totalStudents' => \App\Models\Student::where('is_active', true)->count(),
            'demoStudents' => \App\Models\Student::with('schoolClass')
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(3)
                ->get(),
        ]);
    }

    public function store(Request $request, AttendanceService $service): JsonResponse
    {
        $data = $request->validate([
            'qr_code' => ['required', 'string', 'max:80'],
            'type' => ['required', 'in:in,out'],
        ]);

        $result = $service->scan($data['qr_code'], $data['type']);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['code'] ?? 422);
        }

        $student = $result['student'];
        $attendance = $result['attendance'];

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'time' => $result['time'],
            'type' => $data['type'],
            'late_minutes' => $result['late_minutes'],
            'student' => [
                'name' => $student->name,
                'nisn' => $student->nisn,
                'class' => $student->schoolClass?->name ?? '-',
                'department' => $student->schoolClass?->department ?? '-',
                'gender' => $student->genderLabel(),
                'parent_name' => $student->parent_name,
                'parent_phone' => $student->parent_phone,
                'initials' => $student->initials(),
                'photo_url' => $student->photoUrl(),
            ],
            'attendance' => [
                'status' => $attendance->status,
                'status_label' => $attendance->statusLabel(),
                'time_in' => $attendance->time_in ? substr($attendance->time_in, 0, 5) : null,
                'time_out' => $attendance->time_out ? substr($attendance->time_out, 0, 5) : null,
                'notification_status' => $attendance->notification_status,
            ],
            'wa_message' => $result['wa_message'],
        ]);
    }

    public function recent(): JsonResponse
    {
        $items = Attendance::query()
            ->with(['student.schoolClass'])
            ->whereDate('date', Carbon::today()->toDateString())
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (Attendance $attendance) => [
                'name' => $attendance->student?->name,
                'initials' => $attendance->student?->initials(),
                'class' => $attendance->student?->schoolClass?->name,
                'status' => $attendance->status,
                'status_label' => $attendance->statusLabel(),
                'time' => substr((string) ($attendance->time_out ?: $attendance->time_in), 0, 5),
                'notification' => $attendance->notification_status,
            ]);

        return response()->json([
            'items' => $items,
            'stats' => [
                'total' => Attendance::whereDate('date', Carbon::today()->toDateString())->count(),
                'hadir' => Attendance::whereDate('date', Carbon::today()->toDateString())->where('status', Attendance::STATUS_HADIR)->count(),
                'terlambat' => Attendance::whereDate('date', Carbon::today()->toDateString())->where('status', Attendance::STATUS_TERLAMBAT)->count(),
                'pulang' => Attendance::whereDate('date', Carbon::today()->toDateString())->whereNotNull('time_out')->count(),
            ],
        ]);
    }
}
