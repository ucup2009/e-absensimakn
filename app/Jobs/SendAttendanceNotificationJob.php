<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\WhatsAppGatewayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendAttendanceNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 30];

    public function __construct(
        public int $attendanceId,
        public string $type = 'in',
    ) {}

    public function handle(WhatsAppGatewayService $whatsapp): void
    {
        $attendance = Attendance::with('student.schoolClass')->find($this->attendanceId);

        if (! $attendance || ! $attendance->student) {
            return;
        }

        $student = $attendance->student;

        if (! filled($student->parent_phone)) {
            $attendance->update(['notification_status' => Attendance::NOTIF_FAILED]);

            return;
        }

        $templateKey = $whatsapp->templateKeyFor($attendance, $this->type);
        $message = $whatsapp->renderTemplate($templateKey, $student, $attendance, [
            'nama_wali' => $student->parent_name ?? 'Bapak/Ibu',
        ]);

        $sent = $whatsapp->send(
            $student->parent_phone,
            $message,
            $this->type === 'out' ? 'check_out' : 'check_in',
            $student,
            $attendance,
        );

        $attendance->update([
            'notification_status' => $sent ? Attendance::NOTIF_SENT : Attendance::NOTIF_FAILED,
            'notification_sent_at' => $sent ? now() : null,
        ]);

        if (! $sent) {
            Log::warning('Notifikasi WA gagal dikirim', ['attendance_id' => $attendance->id]);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Attendance::whereKey($this->attendanceId)->update([
            'notification_status' => Attendance::NOTIF_FAILED,
        ]);
    }
}
