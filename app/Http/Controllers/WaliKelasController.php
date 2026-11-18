<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Services\AttendanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WaliKelasController extends Controller
{
    public function index(Request $request, AttendanceReportService $reports): View
    {
        $user = $request->user();
        $class = $user->homeroomClass;

        if (! $class) {
            return view('wali.dashboard', [
                'class' => null,
                'date' => Carbon::today(),
                'students' => collect(),
                'counts' => [],
                'monthRate' => 0,
                'weekly' => collect(),
                'notRecorded' => 0,
            ]);
        }

        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $students = $class->students()
            ->where('is_active', true)
            ->with(['attendances' => fn ($q) => $q->whereDate('date', $date->toDateString())])
            ->orderBy('name')
            ->get();

        $counts = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
        ];

        foreach ($students as $student) {
            $attendance = $student->attendances->first();
            if ($attendance) {
                $counts[$attendance->status] = ($counts[$attendance->status] ?? 0) + 1;
            }
        }

        $recorded = array_sum($counts);
        $notRecorded = max(0, $students->count() - $recorded);

        $monthRate = $class->attendancePercentage($date->copy()->startOfMonth()->toDateString(), $date->toDateString());

        return view('wali.dashboard', [
            'class' => $class,
            'date' => $date,
            'students' => $students,
            'counts' => $counts,
            'monthRate' => $monthRate,
            'weekly' => $reports->weeklyTrend($user, $class->id),
            'notRecorded' => $notRecorded,
            'target' => (int) Setting::get('attendance_target', 90),
        ]);
    }

    public function sendWarnings(Request $request)
    {
        $user = $request->user();
        $class = $user->homeroomClass;

        abort_unless($class, 403, 'Anda belum ditetapkan sebagai wali kelas.');

        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $students = $class->students()
            ->where('is_active', true)
            ->whereDoesntHave('attendances', fn ($q) => $q
                ->whereDate('date', $date->toDateString())
                ->whereIn('status', [Attendance::STATUS_HADIR, Attendance::STATUS_TERLAMBAT]))
            ->get();

        $service = app(\App\Services\WhatsAppGatewayService::class);

        foreach ($students as $student) {
            $message = $service->renderTemplate('late_warning', $student, null, [
                'tanggal' => $date->translatedFormat('d F Y'),
            ]);
            $service->send($student->parent_phone ?? '', $message, 'warning', $student);
        }

        return back()->with('success', $students->count().' pesan peringatan WhatsApp telah masuk ke antrean pengiriman.');
    }
}
