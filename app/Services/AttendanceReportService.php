<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportService
{
    public function query(array $filters, User $user): Builder
    {
        $from = $filters['from'] ?? now()->toDateString();
        $to = $filters['to'] ?? $from;

        $query = Attendance::query()
            ->with(['student.schoolClass'])
            ->betweenDates($from, $to)
            ->when($filters['class_id'] ?? null, function ($q) use ($filters) {
                $q->whereHas('student', fn ($s) => $s->where('class_id', $filters['class_id']));
            })
            ->when($filters['status'] ?? null, fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['q'] ?? null, function ($q) use ($filters) {
                $term = $filters['q'];
                $q->whereHas('student', function ($s) use ($term) {
                    $s->where('name', 'like', "%{$term}%")
                        ->orWhere('nisn', 'like', "%{$term}%");
                });
            });

        if (! $user->isAdmin()) {
            $classId = $user->homeroomClass?->id;
            $query->whereHas('student', fn ($s) => $s->where('class_id', $classId ?? 0));
        }

        return $query->orderByDesc('date')->orderBy('time_in');
    }

    /**
     * @return array{total:int, hadir:int, terlambat:int, izin:int, sakit:int, alpa:int, rate:float, notifications:int}
     */
    public function summary(array $filters, User $user): array
    {
        $query = $this->query($filters, $user);

        $total = (clone $query)->count();
        // reorder() membuang ORDER BY bawaan query; MySQL only_full_group_by menolak
        // ORDER BY pada kolom yang tidak ada di GROUP BY.
        $byStatus = (clone $query)->reorder()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        $hadir = (int) ($byStatus[Attendance::STATUS_HADIR] ?? 0);
        $terlambat = (int) ($byStatus[Attendance::STATUS_TERLAMBAT] ?? 0);

        return [
            'total' => $total,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => (int) ($byStatus[Attendance::STATUS_IZIN] ?? 0),
            'sakit' => (int) ($byStatus[Attendance::STATUS_SAKIT] ?? 0),
            'alpa' => (int) ($byStatus[Attendance::STATUS_ALPA] ?? 0),
            'rate' => $total > 0 ? round(($hadir + $terlambat) / $total * 100, 1) : 0.0,
            'notifications' => (clone $query)->where('notification_status', Attendance::NOTIF_SENT)->count(),
        ];
    }

    public function pdf(array $filters, User $user)
    {
        $limit = 1000;
        $attendances = $this->query($filters, $user)->limit($limit + 1)->get();
        $truncated = $attendances->count() > $limit;
        $attendances = $attendances->take($limit);
        $summary = $this->summary($filters, $user);
        $class = isset($filters['class_id'])
            ? SchoolClass::find($filters['class_id'])
            : ($user->isAdmin() ? null : $user->homeroomClass);

        $pdf = Pdf::loadView('attendances.pdf', [
            'attendances' => $attendances,
            'summary' => $summary,
            'class' => $class,
            'from' => $filters['from'] ?? now()->toDateString(),
            'to' => $filters['to'] ?? now()->toDateString(),
            'printedBy' => $user->name,
            'truncated' => $truncated,
            'rowLimit' => $limit,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rekap-absensi-'.now()->format('Ymd-His').'.pdf');
    }

    public function excel(array $filters, User $user): StreamedResponse
    {
        $attendances = $this->query($filters, $user)->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi');

        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'REKAPITULASI ABSENSI SISWA - MAKN ENDE');
        $sheet->mergeCells('A2:K2');
        $sheet->setCellValue('A2', 'Periode '.($filters['from'] ?? now()->toDateString()).' s/d '.($filters['to'] ?? now()->toDateString()).' | Dicetak oleh: '.$user->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Durasi', 'Status', 'Notifikasi WA', 'Keterangan'];
        $sheet->fromArray($headers, null, 'A4');

        $row = 5;
        foreach ($attendances as $index => $attendance) {
            $sheet->fromArray([
                $index + 1,
                $attendance->student?->nisn,
                $attendance->student?->name,
                $attendance->student?->schoolClass?->name,
                $attendance->date?->format('d/m/Y'),
                $attendance->time_in ? substr($attendance->time_in, 0, 5) : '-',
                $attendance->time_out ? substr($attendance->time_out, 0, 5) : '-',
                $attendance->durationLabel(),
                $attendance->statusLabel(),
                $attendance->notificationLabel(),
                $attendance->note,
            ], null, 'A'.$row);
            $row++;
        }

        $lastRow = max($row - 1, 4);
        $sheet->getStyle('A4:K4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A4:K4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0D5C3A');
        $sheet->getStyle('A4:K'.$lastRow)->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getStyle('A4:K4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'rekap-absensi-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return Collection<int, array{date:string, label:string, hadir:int, terlambat:int, total:int}>
     */
    public function weeklyTrend(?User $user = null, ?int $classId = null): Collection
    {
        $days = collect(range(6, 0))->map(fn ($offset) => now()->subDays($offset));

        return $days->map(function ($day) use ($user, $classId) {
            $query = Attendance::whereDate('date', $day->toDateString());
            $scopeClass = $classId ?? (! $user?->isAdmin() ? $user?->homeroomClass?->id : null);

            if ($scopeClass) {
                $query->whereHas('student', fn ($s) => $s->where('class_id', $scopeClass));
            }

            return [
                'date' => $day->toDateString(),
                'label' => $day->translatedFormat('D'),
                'hadir' => (clone $query)->where('status', Attendance::STATUS_HADIR)->count(),
                'terlambat' => (clone $query)->where('status', Attendance::STATUS_TERLAMBAT)->count(),
                'total' => (clone $query)->whereIn('status', [Attendance::STATUS_HADIR, Attendance::STATUS_TERLAMBAT])->count(),
            ];
        });
    }
}
