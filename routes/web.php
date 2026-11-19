<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaliKelasController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/wali-kelas', [WaliKelasController::class, 'index'])->name('wali.dashboard');
    Route::post('/wali-kelas/peringatan', [WaliKelasController::class, 'sendWarnings'])->name('wali.warnings');

    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');
    Route::get('/scan/recent', [ScanController::class, 'recent'])->name('scan.recent');

    Route::get('/presensi', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/presensi', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('/presensi/export/pdf', [AttendanceController::class, 'exportPdf'])->name('attendances.pdf');
    Route::get('/presensi/export/excel', [AttendanceController::class, 'exportExcel'])->name('attendances.excel');

    Route::get('/asisten', [AssistantController::class, 'index'])->name('assistant.index');
    Route::post('/asisten', [AssistantController::class, 'ask'])->name('assistant.ask');

    Route::middleware('role:admin')->group(function () {
        Route::get('/siswa/kartu/cetak', [StudentController::class, 'printCards'])->name('students.cards');
        Route::get('/siswa/{student}/kartu', [StudentController::class, 'card'])->name('students.card');
        Route::resource('siswa', StudentController::class)
            ->except('show')
            ->parameters(['siswa' => 'student'])
            ->names('students');

        Route::resource('kelas', SchoolClassController::class)
            ->except('show')
            ->parameters(['kelas' => 'class'])
            ->names('classes');

        Route::resource('pengguna', UserController::class)
            ->except('show')
            ->parameters(['pengguna' => 'user'])
            ->names('users');

        Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
        Route::put('/whatsapp/template/{template}', [WhatsappController::class, 'updateTemplate'])->name('whatsapp.templates.update');
        Route::post('/whatsapp/uji', [WhatsappController::class, 'test'])->name('whatsapp.test');
        Route::post('/whatsapp/log/{log}/kirim-ulang', [WhatsappController::class, 'resend'])->name('whatsapp.resend');

        Route::get('/pengaturan', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/pengaturan/queue/restart', [SettingController::class, 'restartQueue'])->name('settings.queue.restart');
        Route::post('/pengaturan/cache/flush', [SettingController::class, 'flushCache'])->name('settings.cache.flush');
        Route::post('/pengaturan/backup', [SettingController::class, 'backup'])->name('settings.backup');
        Route::get('/pengaturan/backup/{file}', [SettingController::class, 'downloadBackup'])->name('settings.backup.download');
    });
});
