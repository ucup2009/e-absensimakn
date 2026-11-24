<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyiapkan aplikasi setelah deploy.
 *
 * DatabaseSeeder berisi INSERT mentah untuk siswa dan presensi, jadi hanya
 * boleh dijalankan sekali saat database benar-benar masih kosong. Perintah ini
 * dipakai sebagai job `post-deployment` di Wasmer sehingga deploy berikutnya
 * tidak menimpa data sekolah.
 */
class DeployPrepare extends Command
{
    protected $signature = 'deploy:prepare {--force : Jalankan seeder walau tabel users sudah berisi data}';

    protected $description = 'Siapkan data awal (seed sekali) dan cache produksi setelah deploy';

    public function handle(): int
    {
        if (! Schema::hasTable('users')) {
            $this->warn('Tabel users belum ada — migrasi sepertinya gagal. Lewati seeding.');

            return self::FAILURE;
        }

        if (! $this->option('force') && User::query()->exists()) {
            $this->info('Database sudah berisi data (users > 0). Seeding dilewati.');

            return self::SUCCESS;
        }

        $this->info('Database masih kosong — mengisi data contoh sekali...');

        Artisan::call('db:seed', ['--force' => true], $this->getOutput());

        $this->info('Seeding selesai: '.User::query()->count().' pengguna.');

        // Pastikan storage publik tersedia tanpa `storage:link` (Wasmer memakai volume).
        foreach (['users', 'students', 'backups'] as $dir) {
            $path = storage_path('app/public/'.$dir);

            if (! is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }

        $this->info('Data awal siap.');

        return self::SUCCESS;
    }
}
