<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(protected WhatsAppGatewayService $whatsapp) {}

    public function now(): Carbon
    {
        return Carbon::now((string) Setting::get('timezone', 'Asia/Makassar'));
    }

    public function ontimeLimit(): string
    {
        return (string) Setting::get('check_in_limit', '07:15');
    }

    public function checkInStart(): string
    {
        return (string) Setting::get('check_in_start', '06:30');
    }

    public function checkOutTime(): string
    {
        $isFriday = $this->now()->isFriday();

        return (string) Setting::get($isFriday ? 'friday_check_out' : 'check_out_time', $isFriday ? '11:30' : '15:30');
    }

    /**
     * Proses pemindaian QR kartu pelajar.
     *
     * @return array{success: bool, message: string, code: int, student?: Student, attendance?: Attendance, time?: string, wa_message?: string, late_minutes?: int}
     */
    public function scan(string $payload, string $type = 'in'): array
    {
        $payload = trim($payload);

        $student = Student::with('schoolClass')
            ->where('qr_code', $payload)
            ->orWhere('nisn', $payload)
            ->first();

        if (! $student) {
            return ['success' => false, 'message' => 'Kartu pelajar / NISN tidak dikenali. Silakan hubungi operator.', 'code' => 404];
        }

        if (! $student->is_active) {
            return ['success' => false, 'message' => 'Status siswa tidak aktif. Hubungi operator madrasah.', 'code' => 422];
        }

        if ($this->isHoliday()) {
            return ['success' => false, 'message' => 'Mode libur/tanggal merah aktif. Presensi tidak dicatat.', 'code' => 422];
        }

        $now = $this->now();
        $today = $now->toDateString();

        $attendance = $this->findAttendance($student->id, $today);

        if ($type === 'out') {
            if (! $attendance->time_in) {
                return ['success' => false, 'message' => 'Siswa belum melakukan presensi masuk hari ini.', 'code' => 422];
            }

            if ($attendance->time_out) {
                return ['success' => false, 'message' => 'Presensi pulang sudah tercatat pukul '.substr($attendance->time_out, 0, 5).' WITA.', 'code' => 422];
            }

            $attendance->time_out = $now->format('H:i:s');
            $attendance->recorded_by = auth()->id();
            $attendance->save();
        } else {
            if ($attendance->time_in) {
                return ['success' => false, 'message' => 'Siswa sudah melakukan presensi masuk pukul '.substr($attendance->time_in, 0, 5).' WITA.', 'code' => 422];
            }

            $attendance->time_in = $now->format('H:i:s');
            $attendance->status = $now->format('H:i:s') > $this->ontimeLimit()
                ? Attendance::STATUS_TERLAMBAT
                : Attendance::STATUS_HADIR;
            $attendance->notification_status = Attendance::NOTIF_PENDING;
            $attendance->recorded_by = auth()->id();
            $attendance->save();
        }

        $attendance->refresh()->load('student.schoolClass');

        $templateKey = $this->whatsapp->templateKeyFor($attendance, $type);
        $waMessage = $this->whatsapp->renderTemplate($templateKey, $student, $attendance, [
            'time' => $now->format('H:i'),
        ]);

        // Kirim asynchronous agar proses scan tetap di bawah 1 detik.
        \App\Jobs\SendAttendanceNotificationJob::dispatch($attendance->id, $type);

        $lateMinutes = max(0, $this->minutes($attendance->time_in) - $this->minutes($this->ontimeLimit()));

        return [
            'success' => true,
            'message' => $type === 'in'
                ? 'Presensi masuk berhasil dicatat.'
                : 'Presensi pulang berhasil dicatat.',
            'code' => 200,
            'student' => $student,
            'attendance' => $attendance,
            'time' => $now->format('H:i:s'),
            'wa_message' => $waMessage,
            'late_minutes' => $lateMinutes,
        ];
    }

    public function isHoliday(): bool
    {
        return (bool) Setting::get('holiday_mode', false);
    }

    /**
     * Cari presensi harian siswa. Kolom date dapat tersimpan sebagai
     * "Y-m-d" maupun "Y-m-d H:i:s", sehingga pencarian memakai whereDate.
     */
    protected function findAttendance(int $studentId, string $date): Attendance
    {
        return Attendance::query()
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->first()
            ?? new Attendance([
                'student_id' => $studentId,
                'date' => $date,
            ]);
    }

    protected function minutes(?string $time): int
    {
        if (! $time) {
            return 0;
        }

        [$h, $m] = array_pad(explode(':', $time), 2, 0);

        return ((int) $h * 60) + (int) $m;
    }

    public function manualStore(Student $student, array $data): Attendance
    {
        $attendance = $this->findAttendance($student->id, $data['date']);

        $attendance->fill([
            'time_in' => $data['time_in'] ?? $attendance->time_in,
            'time_out' => $data['time_out'] ?? $attendance->time_out,
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'recorded_by' => auth()->id(),
        ])->save();

        return $attendance;
    }
}
