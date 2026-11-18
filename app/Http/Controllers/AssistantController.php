<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function index(Request $request): View
    {
        $prompt = $request->session()->get('assistant_prompt');
        $answer = $request->session()->get('assistant_answer');

        return view('assistant.index', [
            'insights' => $this->insights(),
            'prompt' => $prompt,
            'answer' => $answer,
        ]);
    }

    public function ask(Request $request): View
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:500'],
        ]);

        $insights = $this->insights();
        $answer = $this->answer($data['prompt'], $insights);

        return view('assistant.index', [
            'insights' => $insights,
            'prompt' => $data['prompt'],
            'answer' => $answer,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function insights(): array
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now();

        $query = Attendance::query()->betweenDates($from->toDateString(), $to->toDateString());

        $total = (clone $query)->count();
        $hadir = (clone $query)->where('status', Attendance::STATUS_HADIR)->count();
        $terlambat = (clone $query)->where('status', Attendance::STATUS_TERLAMBAT)->count();
        $izin = (clone $query)->where('status', Attendance::STATUS_IZIN)->count();
        $sakit = (clone $query)->where('status', Attendance::STATUS_SAKIT)->count();
        $alpa = (clone $query)->where('status', Attendance::STATUS_ALPA)->count();
        $rate = $total > 0 ? round(($hadir + $terlambat) / $total * 100, 1) : 0;

        $classRates = SchoolClass::query()
            ->withCount(['students as student_count' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(fn (SchoolClass $class) => [
                'name' => $class->name,
                'rate' => $class->attendancePercentage($from->toDateString(), $to->toDateString()),
                'students' => $class->student_count,
            ])
            ->sortBy('rate')
            ->values();

        $topLate = Student::query()
            ->with('schoolClass')
            ->withCount(['attendances as late_count' => fn ($q) => $q
                ->betweenDates($from->toDateString(), $to->toDateString())
                ->where('status', Attendance::STATUS_TERLAMBAT)])
            ->orderByDesc('late_count')
            ->limit(10)
            ->get()
            ->filter(fn (Student $student) => $student->late_count > 0)
            ->take(5)
            ->values();

        $waTotal = WhatsappLog::where('created_at', '>=', $from)->count();
        $waFailed = WhatsappLog::where('created_at', '>=', $from)->where('status', 'failed')->count();

        return [
            'from' => $from,
            'to' => $to,
            'total' => $total,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpa' => $alpa,
            'rate' => $rate,
            'classRates' => $classRates,
            'topLate' => $topLate,
            'waTotal' => $waTotal,
            'waFailed' => $waFailed,
        ];
    }

    /**
     * Analisis lokal berbasis aturan (tanpa mengirim data ke layanan eksternal).
     *
     * @param  array<string, mixed>  $insights
     */
    protected function answer(string $prompt, array $insights): string
    {
        $lower = mb_strtolower($prompt);
        $lines = [];

        $lines[] = 'Analisis periode '.$insights['from']->translatedFormat('d F').' - '.$insights['to']->translatedFormat('d F Y').':';
        $lines[] = '• Tingkat kehadiran keseluruhan mencapai '.$insights['rate'].'% dari '.$insights['total'].' rekaman presensi.';
        $lines[] = '• Rincian: hadir '.$insights['hadir'].', terlambat '.$insights['terlambat'].', izin '.$insights['izin'].', sakit '.$insights['sakit'].', alpa '.$insights['alpa'].'.';

        if (str_contains($lower, 'kelas') || str_contains($lower, 'jurusan') || str_contains($lower, 'rombel')) {
            $worst = $insights['classRates']->first();
            $best = $insights['classRates']->last();
            if ($worst) {
                $lines[] = '';
                $lines[] = 'Kelas dengan kehadiran terendah: '.$worst['name'].' ('.$worst['rate'].'%).';
                $lines[] = 'Kelas dengan kehadiran tertinggi: '.($best['name'] ?? '-').' ('.($best['rate'] ?? 0).'%).';
                $lines[] = 'Rekomendasi: lakukan pendampingan wali kelas dan konfirmasi ke orang tua siswa dengan absensi rendah.';
            }
        }

        if (str_contains($lower, 'terlambat') || str_contains($lower, 'disiplin')) {
            $lines[] = '';
            if ($insights['topLate']->isEmpty()) {
                $lines[] = 'Tidak ditemukan siswa dengan keterlambatan pada periode ini.';
            } else {
                $lines[] = 'Siswa dengan keterlambatan tertinggi:';
                foreach ($insights['topLate'] as $student) {
                    $lines[] = '  - '.$student->name.' ('.$student->schoolClass?->name.'): '.$student->late_count.' kali.';
                }
                $lines[] = 'Rekomendasi: kirim peringatan WhatsApp bertahap dan catat dalam pembinaan BK.';
            }
        }

        if (str_contains($lower, 'wa') || str_contains($lower, 'whatsapp') || str_contains($lower, 'notifikasi')) {
            $failRate = $insights['waTotal'] > 0 ? round($insights['waFailed'] / $insights['waTotal'] * 100, 1) : 0;
            $lines[] = '';
            $lines[] = 'Kanal WhatsApp: '.$insights['waTotal'].' pesan diproses, '.$insights['waFailed'].' gagal ('.$failRate.'%).';
            $lines[] = $failRate > 5
                ? 'Rekomendasi: periksa koneksi gateway dan kuota sebelum jam masuk berikutnya.'
                : 'Kondisi gateway relatif sehat. Lanjutkan pemantauan antrean harian.';
        }

        if (str_contains($lower, 'laporan') || str_contains($lower, 'rekap') || str_contains($lower, 'export')) {
            $lines[] = '';
            $lines[] = 'Untuk laporan resmi, buka menu Data Presensi lalu gunakan tombol Export PDF atau Export Excel. Filter kelas dan periode terlebih dahulu agar rekap sesuai standar EMIS.';
        }

        $lines[] = '';
        $lines[] = 'Catatan: asisten ini menganalisis data internal secara lokal (rule-based) tanpa mengirim data siswa ke layanan AI eksternal.';

        return implode("\n", $lines);
    }
}
