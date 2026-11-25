<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Mengekspor isi database menjadi satu berkas JSON + dokumentasi skema.
 *
 * Hasilnya dipakai untuk memberi data nyata ke tool AI (misalnya Google AI
 * Studio) yang tidak punya koneksi MySQL langsung.
 *
 *   php artisan export:dataset
 *   php artisan export:dataset --with-secrets
 */
class ExportDataset extends Command
{
    protected $signature = 'export:dataset
                            {--path= : Folder tujuan (default storage/app/exports)}
                            {--with-secrets : Ikutkan hash password dan token (jangan dipakai untuk dibagikan)}';

    protected $description = 'Ekspor data absensi ke JSON + skema untuk dipakai tool AI / aplikasi lain';

    /** Kolom yang tidak boleh ikut kecuali --with-secrets. */
    private const SENSITIVE = [
        'users' => ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'],
    ];

    private const TABLES = [
        'users' => 'Akun admin dan wali kelas',
        'classes' => 'Rombongan belajar / kelas',
        'students' => 'Data siswa dan kartu QR',
        'attendances' => 'Rekaman presensi harian',
        'settings' => 'Pengaturan aplikasi (key-value)',
        'whatsapp_templates' => 'Template pesan WhatsApp',
        'whatsapp_logs' => 'Riwayat pengiriman WhatsApp',
        'audit_logs' => 'Jejak aktivitas pengguna',
    ];

    public function handle(): int
    {
        $dir = $this->option('path') ?: storage_path('app/exports');

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("Tidak bisa membuat folder: {$dir}");

            return self::FAILURE;
        }

        $withSecrets = (bool) $this->option('with-secrets');
        $dataset = [];
        $counts = [];

        foreach (self::TABLES as $table => $description) {
            if (! Schema::hasTable($table)) {
                $this->line("  lewat  {$table} (tabel tidak ada)");

                continue;
            }

            $rows = DB::table($table)->orderBy('id')->get();

            if (! $withSecrets && isset(self::SENSITIVE[$table])) {
                $rows = $rows->map(function ($row) use ($table) {
                    foreach (self::SENSITIVE[$table] as $column) {
                        unset($row->{$column});
                    }

                    return $row;
                });
            }

            $dataset[$table] = $rows->map(fn ($row) => (array) $row)->all();
            $counts[$table] = count($dataset[$table]);

            $this->line(sprintf('  %-20s %5d baris', $table, $counts[$table]));
        }

        $payload = [
            'meta' => [
                'app' => config('app.name'),
                'generated_at' => now()->toIso8601String(),
                'timezone' => config('app.timezone'),
                'counts' => $counts,
                'notes' => $withSecrets
                    ? 'PERINGATAN: berkas ini memuat hash password. Jangan dibagikan.'
                    : 'Kolom sensitif (password, token) sudah dihapus.',
                'relations' => [
                    'classes.homeroom_teacher_id -> users.id',
                    'students.class_id -> classes.id',
                    'attendances.student_id -> students.id',
                    'attendances.recorded_by -> users.id',
                    'whatsapp_logs.student_id -> students.id',
                    'audit_logs.user_id -> users.id',
                ],
                'enums' => [
                    'users.role' => ['admin', 'wali_kelas'],
                    'students.gender' => ['L', 'P'],
                    'attendances.status' => ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'],
                    'attendances.notification_status' => ['pending', 'sent', 'failed', 'skipped'],
                ],
            ],
            'data' => $dataset,
        ];

        $jsonPath = $dir.DIRECTORY_SEPARATOR.'e-absensi-dataset.json';

        file_put_contents(
            $jsonPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $schemaPath = $dir.DIRECTORY_SEPARATOR.'skema.sql';

        file_put_contents($schemaPath, $this->buildSchemaDump());

        $readmePath = $dir.DIRECTORY_SEPARATOR.'BACA-AI-STUDIO.md';

        file_put_contents($readmePath, $this->buildReadme($counts));

        $this->newLine();
        $this->info('Selesai:');
        $this->line('  '.$jsonPath.'  ('.number_format(filesize($jsonPath) / 1024, 1).' KB)');
        $this->line('  '.$schemaPath);
        $this->line('  '.$readmePath);

        return self::SUCCESS;
    }

    /** SHOW CREATE TABLE untuk semua tabel aplikasi (khusus MySQL/MariaDB). */
    private function buildSchemaDump(): string
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return "-- Skema otomatis hanya tersedia untuk MySQL/MariaDB (driver: {$driver}).\n";
        }

        $sql = "-- Struktur tabel E-Absensi\n-- Dibuat: ".now()->toDateTimeString()."\n\n";

        foreach (array_keys(self::TABLES) as $table) {
            try {
                $row = DB::selectOne('SHOW CREATE TABLE `'.$table.'`');
                $create = ((array) $row)['Create Table'] ?? null;

                if ($create) {
                    $sql .= $create.";\n\n";
                }
            } catch (Throwable) {
                // Tabel belum ada — lewati.
            }
        }

        return $sql;
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function buildReadme(array $counts): string
    {
        $rows = '';

        foreach (self::TABLES as $table => $description) {
            $count = $counts[$table] ?? 0;
            $rows .= "| `{$table}` | {$description} | {$count} |\n";
        }

        return <<<MD
        # Dataset E-Absensi untuk Google AI Studio

        Berkas di folder ini dibuat otomatis oleh `php artisan export:dataset`.

        | Berkas | Isi |
        | --- | --- |
        | `e-absensi-dataset.json` | Semua tabel + metadata relasi |
        | `skema.sql` | `SHOW CREATE TABLE` untuk MySQL |
        | `BACA-AI-STUDIO.md` | Petunjuk ini |

        ## Tabel

        | Tabel | Keterangan | Baris |
        | --- | --- | --- |
        {$rows}
        ## Cara memakai di Google AI Studio

        AI Studio **tidak bisa** menyambung ke MySQL. Pilih salah satu:

        ### 1. Tempel sebagai data statis (paling cepat)

        1. Buka `https://aistudio.google.com` → **Build**.
        2. Salin isi `e-absensi-dataset.json` ke prompt, awali dengan:
           *"Berikut dataset JSON aplikasi absensi madrasah. Gunakan sebagai data awal aplikasi React yang akan kamu buat."*
        3. Kalau berkas terlalu besar, kirim per tabel (mulai dari `classes`, `students`, `attendances`).

        ### 2. Jadikan API (data tetap hidup)

        1. Buat endpoint JSON di Laravel, misalnya `GET /api/siswa` dan `GET /api/presensi?from=2026-11-01&to=2026-11-30`.
        2. Aktifkan CORS untuk domain AI Studio.
        3. Buka akses lewat tunnel, contoh:
           `cloudflared tunnel --url http://127.0.0.1:8000`
        4. Beri AI Studio URL tunnel + contoh respons. Ingat: URL tunnel sementara berubah saat dijalankan ulang.

        ## Catatan keamanan

        - Kolom `password` dan token sudah dihapus. Jangan jalankan dengan `--with-secrets` untuk berkas yang akan diunggah.
        - Data siswa berisi nama orang tua dan nomor telepon. Perlakukan sebagai data pribadi.
        - Berkas ini tidak ikut ter-commit karena berada di `storage/`.

        MD;
    }
}
