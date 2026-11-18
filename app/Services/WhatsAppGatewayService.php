<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\Student;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    public function engine(): string
    {
        return (string) Setting::get('wa_engine', 'simulator');
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('wa_notifications_enabled', true);
    }

    public function isSimulation(): bool
    {
        return $this->engine() === 'simulator' || blank(Setting::get('wa_api_key'));
    }

    public function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    public function renderTemplate(string $key, Student $student, ?Attendance $attendance = null, array $extra = []): string
    {
        $template = WhatsappTemplate::query()->where('key', $key)->where('is_active', true)->first();
        $body = $template?->body ?? $this->defaultTemplate($key);

        $replacements = array_merge([
            '{nama_siswa}' => $student->name,
            '{nisn}' => $student->nisn,
            '{kelas}' => $student->schoolClass?->name ?? '-',
            '{nama_wali}' => $student->parent_name ?? 'Bapak/Ibu',
            '{nama_madrasah}' => (string) Setting::get('school_name', 'MAKN Ende'),
            '{tanggal}' => ($attendance?->date ?? today())->translatedFormat('d F Y'),
            '{waktu_scan}' => $extra['time'] ?? now()->format('H:i'),
            '{status}' => $attendance?->statusLabel() ?? '-',
            '{jam_masuk}' => $attendance?->time_in ? substr($attendance->time_in, 0, 5) : '-',
            '{jam_pulang}' => $attendance?->time_out ? substr($attendance->time_out, 0, 5) : '-',
        ], $extra);

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }

    public function defaultTemplate(string $key): string
    {
        return match ($key) {
            'check_in_late' => "*PEMBERITAHUAN PRESENSI*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\nKami informasikan bahwa *{nama_siswa}* ({kelas}) tercatat *TERLAMBAT* masuk madrasah pada {tanggal} pukul {waktu_scan} WITA.\n\nMohon bimbingan dan perhatiannya agar ananda dapat hadir lebih awal. Terima kasih.\n\n_MAKN Ende - Presensi Digital_",
            'check_out' => "*INFORMASI PULANG*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\n*{nama_siswa}* ({kelas}) telah melakukan presensi pulang pada {tanggal} pukul {waktu_scan} WITA.\n\nTerima kasih atas kerja sama Bapak/Ibu.\n\n_MAKN Ende - Presensi Digital_",
            'late_warning' => "*PERINGATAN KETERLAMBATAN*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\nAnanda *{nama_siswa}* ({kelas}) tercatat terlambat/alpa pada {tanggal}. Mohon perhatian dan bimbingannya.\n\n_MAKN Ende - Presensi Digital_",
            default => "*KONFIRMASI KEHADIRAN*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\n*{nama_siswa}* ({kelas}) telah hadir di madrasah pada {tanggal} pukul {waktu_scan} WITA dengan status *{status}*.\n\nTerima kasih.\n\n_MAKN Ende - Presensi Digital_",
        };
    }

    /**
     * Kirim pesan WhatsApp. Mode simulator dipakai otomatis jika API key kosong.
     */
    public function send(string $phone, string $message, string $type = 'attendance', ?Student $student = null, ?Attendance $attendance = null): bool
    {
        $phone = $this->normalizePhone($phone);

        $log = \App\Models\WhatsappLog::create([
            'student_id' => $student?->id,
            'attendance_id' => $attendance?->id,
            'phone' => $phone,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
        ]);

        if (! $this->isEnabled()) {
            $log->update(['status' => 'failed', 'response' => 'Notifikasi WhatsApp dinonaktifkan pada pengaturan.']);

            return false;
        }

        if ($this->isSimulation()) {
            $log->update([
                'status' => 'sent',
                'response' => 'SIMULASI: pesan berhasil diproses tanpa mengirim ke API gateway.',
                'sent_at' => now(),
            ]);

            Log::info('WHATSAPP SIMULATION', ['to' => $phone, 'message' => $message]);

            return true;
        }

        try {
            $endpoint = (string) Setting::get('wa_endpoint', 'https://api.fonnte.com/send');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => (string) Setting::get('wa_api_key'),
            ])->timeout(15)->asForm()->post($endpoint, [
                'target' => $phone,
                'phone' => $phone,
                'message' => $message,
                'countryCode' => '62',
                'delay' => (string) Setting::get('wa_delay', '1.5'),
            ]);

            $success = $response->successful();
            $log->update([
                'status' => $success ? 'sent' : 'failed',
                'response' => mb_substr((string) $response->body(), 0, 1000),
                'sent_at' => $success ? now() : null,
            ]);

            return $success;
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi WhatsApp MAKN Ende: '.$e->getMessage());
            $log->update(['status' => 'failed', 'response' => mb_substr($e->getMessage(), 0, 1000)]);

            return false;
        }
    }

    public function templateKeyFor(Attendance $attendance, string $type): string
    {
        if ($type === 'out') {
            return 'check_out';
        }

        return $attendance->status === Attendance::STATUS_TERLAMBAT ? 'check_in_late' : 'check_in_ontime';
    }

    public function formatTimeWita(?string $time = null): string
    {
        $tz = (string) Setting::get('timezone', 'Asia/Makassar');
        $carbon = $time ? Carbon::parse($time, $tz) : Carbon::now($tz);

        return $carbon->format('H:i:s').' WITA';
    }
}
