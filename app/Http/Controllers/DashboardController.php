<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\AttendanceReportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AttendanceReportService $reports): View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            return redirect()->route('wali.dashboard');
        }

        $today = Carbon::today()->toDateString();

        $totalStudents = Student::query()->where('is_active', true)->count();
        $todayQuery = Attendance::query()->whereDate('date', $today);

        $hadir = (clone $todayQuery)->where('status', Attendance::STATUS_HADIR)->count();
        $terlambat = (clone $todayQuery)->where('status', Attendance::STATUS_TERLAMBAT)->count();
        $izin = (clone $todayQuery)->where('status', Attendance::STATUS_IZIN)->count();
        $sakit = (clone $todayQuery)->where('status', Attendance::STATUS_SAKIT)->count();
        $alpa = (clone $todayQuery)->where('status', Attendance::STATUS_ALPA)->count();
        $recorded = $hadir + $terlambat + $izin + $sakit + $alpa;
        $notRecorded = max(0, $totalStudents - $recorded);
        $rate = $recorded > 0 ? round(($hadir + $terlambat) / $recorded * 100, 1) : 0;

        $recent = Attendance::query()
            ->with(['student.schoolClass'])
            ->whereDate('date', $today)
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $weekly = $reports->weeklyTrend($user);

        $departmentTotals = Student::query()
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->where('students.is_active', true)
            ->selectRaw('classes.department, count(*) as total')
            ->groupBy('classes.department')
            ->pluck('total', 'department');

        $departmentPresent = Attendance::query()
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->whereDate('attendances.date', $today)
            ->whereIn('attendances.status', [Attendance::STATUS_HADIR, Attendance::STATUS_TERLAMBAT])
            ->selectRaw('classes.department, count(*) as present')
            ->groupBy('classes.department')
            ->pluck('present', 'department');

        $departments = $departmentTotals->map(fn ($total, $department) => [
            'name' => $department,
            'total' => (int) $total,
            'present' => (int) ($departmentPresent[$department] ?? 0),
            'percentage' => $total > 0 ? round((int) ($departmentPresent[$department] ?? 0) / $total * 100) : 0,
        ])->sortByDesc('total')->values();

        $waToday = WhatsappLog::query()->whereDate('created_at', $today);
        $waSent = (clone $waToday)->where('status', 'sent')->count();
        $waFailed = (clone $waToday)->where('status', 'failed')->count();
        $waQuota = (int) Setting::get('wa_quota', 2000);
        $waUsed = (int) Setting::get('wa_used', 1284);

        $pendingJobs = DB::table('jobs')->count();

        $topLate = Student::query()
            ->with('schoolClass')
            ->withCount(['attendances as late_count' => fn ($q) => $q
                ->whereMonth('date', now()->month)
                ->where('status', Attendance::STATUS_TERLAMBAT)])
            ->orderByDesc('late_count')
            ->limit(8)
            ->get()
            ->filter(fn (Student $student) => $student->late_count > 0)
            ->take(5)
            ->values();

        return view('dashboard', [
            'today' => Carbon::today(),
            'totalStudents' => $totalStudents,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'recorded' => $recorded,
            'notRecorded' => $notRecorded,
            'rate' => $rate,
            'recent' => $recent,
            'weekly' => $weekly,
            'departments' => $departments,
            'waSent' => $waSent,
            'waFailed' => $waFailed,
            'waQuota' => $waQuota,
            'waUsed' => $waUsed,
            'pendingJobs' => $pendingJobs,
            'topLate' => $topLate,
            'classCount' => SchoolClass::count(),
            'teacherCount' => User::where('role', User::ROLE_WALI)->where('is_active', true)->count(),
            'academicYear' => Setting::get('academic_year', '2025/2026'),
            'semester' => Setting::get('semester', 'Ganjil'),
        ]);
    }
}
