<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use PDO;
use RuntimeException;
use Throwable;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::allCached();

        File::ensureDirectoryExists(storage_path('app/backups'));

        $backups = collect(File::files(storage_path('app/backups')))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->take(5)
            ->map(fn ($file) => [
                'name' => $file->getFilename(),
                'size' => round($file->getSize() / 1024, 1).' KB',
                'date' => Carbon::createFromTimestamp($file->getMTime()),
            ]);

        return view('settings.index', [
            'settings' => $settings,
            'queue' => [
                'driver' => config('queue.default'),
                'pending' => DB::table('jobs')->count(),
                'failed' => DB::table('failed_jobs')->count(),
                'workers' => 4,
                'redis' => false,
            ],
            'audits' => AuditLog::with('user')->latest()->limit(8)->get(),
            'backups' => $backups,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:120'],
            'academic_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:Ganjil,Genap'],
            'timezone' => ['required', 'string', 'max:40'],
            'check_in_start' => ['required', 'date_format:H:i'],
            'check_in_limit' => ['required', 'date_format:H:i', 'after:check_in_start'],
            'check_out_time' => ['required', 'date_format:H:i'],
            'friday_check_out' => ['required', 'date_format:H:i'],
            'late_tolerance' => ['required', 'integer', 'min:0', 'max:120'],
            'attendance_target' => ['required', 'integer', 'min:50', 'max:100'],
            'wa_engine' => ['required', 'in:simulator,fonnte,wablas,baileys'],
            'wa_endpoint' => ['nullable', 'url', 'max:190'],
            'wa_api_key' => ['nullable', 'string', 'max:190'],
            'wa_delay' => ['required', 'numeric', 'min:0.5', 'max:30'],
            'wa_quota' => ['required', 'integer', 'min:0'],
            'wa_used' => ['required', 'integer', 'min:0'],
            'emis_school_code' => ['nullable', 'string', 'max:40'],
            'emis_token' => ['nullable', 'string', 'max:190'],
            'emis_endpoint' => ['nullable', 'url', 'max:190'],
        ]);

        $data['holiday_mode'] = $request->boolean('holiday_mode') ? '1' : '0';
        $data['weather_tolerance'] = $request->boolean('weather_tolerance') ? '1' : '0';
        $data['wa_notifications_enabled'] = $request->boolean('wa_notifications_enabled') ? '1' : '0';
        $data['emis_sync_enabled'] = $request->boolean('emis_sync_enabled') ? '1' : '0';

        foreach ($data as $key => $value) {
            Setting::put($key, $value, $this->groupFor($key));
        }

        AuditLog::record('settings.update', 'Memperbarui pengaturan sistem');

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function restartQueue(): RedirectResponse
    {
        Artisan::call('queue:restart');
        AuditLog::record('queue.restart', 'Restart queue worker');

        return back()->with('success', 'Sinyal restart queue worker berhasil dikirim.');
    }

    public function flushCache(): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        AuditLog::record('cache.flush', 'Membersihkan cache aplikasi');

        return back()->with('success', 'Cache aplikasi dan tampilan berhasil dibersihkan.');
    }

    public function backup(): RedirectResponse
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $stamp = now()->format('Ymd-His');
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $source = database_path('database.sqlite');
            $destination = $directory.'/backup-'.$stamp.'.sqlite';

            if (! File::exists($source)) {
                return back()->with('error', 'Snapshot gagal dibuat: file database tidak ditemukan.');
            }

            File::copy($source, $destination);
        } else {
            $destination = $directory.'/backup-'.$stamp.'.sql';

            try {
                $this->dumpDatabase($destination);
            } catch (Throwable $e) {
                report($e);

                return back()->with('error', 'Snapshot gagal dibuat: '.$e->getMessage());
            }
        }

        AuditLog::record('backup.create', 'Membuat snapshot database '.basename($destination));

        return back()->with('success', 'Snapshot database berhasil dibuat: '.basename($destination));
    }

    /**
     * Dump seluruh tabel MySQL ke file .sql tanpa bergantung pada binary mysqldump.
     */
    protected function dumpDatabase(string $destination): void
    {
        $pdo = DB::connection()->getPdo();
        $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $handle = fopen($destination, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Tidak dapat menulis file snapshot.');
        }

        fwrite($handle, "-- E-Absensi MAKN Ende\n");
        fwrite($handle, "-- Database: {$database}\n");
        fwrite($handle, '-- Dibuat: '.now()->format('Y-m-d H:i:s')." WITA\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($tables as $table) {
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);

            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n".$create[1].";\n\n");

            // FETCH_NUM penting: mode default (BOTH) membuat setiap nilai muncul
            // dua kali dan menghasilkan INSERT dengan jumlah nilai berlebih.
            $statement = $pdo->query("SELECT * FROM `{$table}`");
            $statement->setFetchMode(PDO::FETCH_NUM);

            foreach ($statement as $row) {
                $values = array_map(
                    fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value),
                    $row
                );

                fwrite($handle, "INSERT INTO `{$table}` VALUES (".implode(',', $values).");\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    public function downloadBackup(string $file)
    {
        $path = storage_path('app/backups/'.basename($file));

        abort_unless(File::exists($path), 404);

        AuditLog::record('backup.download', 'Mengunduh snapshot '.basename($file));

        return response()->download($path);
    }

    protected function groupFor(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'wa_') => 'whatsapp',
            str_starts_with($key, 'emis_') => 'emis',
            str_starts_with($key, 'check_'), in_array($key, ['friday_check_out', 'late_tolerance', 'holiday_mode', 'weather_tolerance', 'attendance_target']) => 'attendance',
            default => 'general',
        };
    }
}
