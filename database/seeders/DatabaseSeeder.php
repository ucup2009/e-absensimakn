<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedClasses();
        $this->seedStudents();
        $this->seedSettings();
        $this->seedTemplates();
        $this->seedAttendances();
        $this->seedWhatsappLogs();
        $this->seedAuditLogs();
    }

    protected function seedUsers(): void
    {
        User::updateOrCreate(['email' => 'admin@maknende.sch.id'], [
            'name' => 'Ust. Mansyur Hidayat',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'nip' => '197805122006041002',
            'phone' => '6281234567001',
            'is_active' => true,
        ]);

        $teachers = [
            'Ust. Ahmad Fauzi, S.Pd.',
            'Ustazah Fatimah Azzahra, S.Pd.',
            'Ust. Muhammad Ridwan, S.Kom.',
            'Ustazah Nurul Hidayah, S.Pd.',
            'Ust. Abdul Rahman, S.T.',
            'Ustazah Siti Aisyah, S.Pd.',
            'Ust. Hasan Basri, S.Kom.',
            'Ustazah Dewi Lestari, S.Pd.',
            'Ust. Zainal Abidin, S.Pd.',
            'Ustazah Rahma Salsabila, S.Pd.',
            'Ust. Farhan Maulana, S.Kom.',
            'Ustazah Intan Permata, S.Pd.',
            'Ust. Ilham Ramadhan, S.T.',
            'Ustazah Salma Maharani, S.Pd.',
            'Ust. Rizky Firmansyah, S.Kom.',
            'Ustazah Anisa Putri, S.Pd.',
            'Ust. Yusuf Setiawan, S.Pd.',
            'Ustazah Hana Anggraini, S.Pd.',
            'Ust. Bagus Wibowo, S.Pd.',
            'Ustazah Fitri Handayani, S.Pd.',
            'Ust. Dimas Kurniawan, S.Kom.',
            'Ustazah Zahra Nabila, S.Pd.',
            'Ust. Irfan Saputra, S.Pd.',
            'Ustazah Wulan Sari, S.Pd.',
        ];

        foreach ($teachers as $index => $name) {
            $slug = 'wali'.str_pad($index + 1, 2, '0', STR_PAD_LEFT);

            User::updateOrCreate(['email' => $slug.'@maknende.sch.id'], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role' => User::ROLE_WALI,
                'nip' => '1985'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'201001'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'phone' => '6281234567'.str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }
    }

    protected function seedClasses(): void
    {
        $definitions = [
            ['X RPL 1', 'X', 'RPL'],
            ['X RPL 2', 'X', 'RPL'],
            ['X TKJ 1', 'X', 'TKJ'],
            ['X TKJ 2', 'X', 'TKJ'],
            ['X ATPH 1', 'X', 'ATPH'],
            ['X DKV 1', 'X', 'DKV'],
            ['X Keagamaan 1', 'X', 'Keagamaan'],
            ['XI RPL 1', 'XI', 'RPL'],
            ['XI RPL 2', 'XI', 'RPL'],
            ['XI TKJ 1', 'XI', 'TKJ'],
            ['XI TKJ 2', 'XI', 'TKJ'],
            ['XI ATPH 1', 'XI', 'ATPH'],
            ['XI DKV 1', 'XI', 'DKV'],
            ['XI Keagamaan 1', 'XI', 'Keagamaan'],
            ['XII RPL 1', 'XII', 'RPL'],
            ['XII TKJ 1', 'XII', 'TKJ'],
            ['XII ATPH 1', 'XII', 'ATPH'],
            ['XII Keagamaan 1', 'XII', 'Keagamaan'],
        ];

        $teachers = User::where('role', User::ROLE_WALI)->orderBy('id')->get();

        foreach ($definitions as $index => [$name, $level, $department]) {
            SchoolClass::updateOrCreate(['name' => $name, 'academic_year' => '2025/2026'], [
                'level' => $level,
                'department' => $department,
                'homeroom_teacher_id' => $teachers[$index]->id ?? null,
                'capacity' => 27,
            ]);
        }
    }

    protected function seedStudents(): void
    {
        $maleFirst = ['Muhammad', 'Ahmad', 'Abdul', 'Fajar', 'Rizky', 'Ilham', 'Yusuf', 'Hasan', 'Husain', 'Zainal', 'Farhan', 'Aditya', 'Bagus', 'Dimas', 'Fikri', 'Gilang', 'Irfan', 'Joko', 'Khalid', 'Lukman', 'Naufal', 'Rafi', 'Sultan', 'Taufik'];
        $femaleFirst = ['Siti', 'Nur', 'Fatimah', 'Aisyah', 'Zahra', 'Putri', 'Dewi', 'Intan', 'Lestari', 'Rahma', 'Salma', 'Anisa', 'Bunga', 'Citra', 'Dinda', 'Fitri', 'Hana', 'Indah', 'Kartika', 'Maya', 'Nabila', 'Rani', 'Wulan', 'Zulfa'];
        $lastNames = ['Abdullah', 'Rahman', 'Hidayat', 'Pratama', 'Saputra', 'Nugroho', 'Firmansyah', 'Maulana', 'Ramadhan', 'Setiawan', 'Wibowo', 'Kurniawan', 'Lestari', 'Handayani', 'Salsabila', 'Azzahra', 'Permata', 'Anggraini', 'Maharani', 'Putri'];

        $classes = SchoolClass::orderBy('id')->get();
        $rows = [];
        $sequence = 0;

        foreach ($classes as $classIndex => $class) {
            for ($i = 0; $i < 27; $i++) {
                $sequence++;
                $gender = $sequence % 2 === 0 ? 'P' : 'L';
                $first = $gender === 'P' ? $femaleFirst[($sequence * 3) % count($femaleFirst)] : $maleFirst[($sequence * 5) % count($maleFirst)];
                $last = $lastNames[($sequence * 7) % count($lastNames)];
                $middle = $gender === 'P' ? $femaleFirst[($sequence * 11) % count($femaleFirst)] : $maleFirst[($sequence * 13) % count($maleFirst)];
                $name = $first.' '.$middle.' '.$last;
                $nisn = '00'.str_pad((string) (100000 + $sequence), 8, '0', STR_PAD_LEFT);
                $parentName = ($gender === 'P' ? 'Bapak ' : 'Bapak ').$maleFirst[($sequence * 17) % count($maleFirst)].' '.$last;
                $phone = '62812'.str_pad((string) (7000000 + $sequence), 7, '0', STR_PAD_LEFT);

                $rows[] = [
                    'nisn' => $nisn,
                    'name' => $name,
                    'class_id' => $class->id,
                    'gender' => $gender,
                    'parent_name' => $parentName,
                    'parent_phone' => $phone,
                    'address' => 'Desa '.$lastNames[($sequence * 19) % count($lastNames)].', Ende, Nusa Tenggara Timur',
                    'birth_date' => Carbon::create(2006 + ($classIndex % 4), (($sequence % 12) + 1), (($sequence % 27) + 1))->toDateString(),
                    'qr_code' => 'MAKN-'.$nisn,
                    'qr_generated_at' => now(),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('students')->insert($chunk);
        }
    }

    protected function seedSettings(): void
    {
        $settings = [
            'school_name' => 'MAKN Ende',
            'school_level' => 'Madrasah Aliyah Kejuruan Negeri',
            'school_region' => 'Wilayah Flores / NTT',
            'academic_year' => '2025/2026',
            'semester' => 'Ganjil',
            'timezone' => 'Asia/Makassar',
            'check_in_start' => '06:30',
            'check_in_limit' => '07:15',
            'check_out_time' => '15:30',
            'friday_check_out' => '11:30',
            'late_tolerance' => '15',
            'attendance_target' => '90',
            'holiday_mode' => '0',
            'weather_tolerance' => '0',
            'wa_engine' => 'simulator',
            'wa_endpoint' => 'https://api.fonnte.com/send',
            'wa_api_key' => '',
            'wa_delay' => '1.5',
            'wa_notifications_enabled' => '1',
            'wa_quota' => '2000',
            'wa_used' => '1284',
            'emis_school_code' => 'MAKN-ENDE-5307',
            'emis_endpoint' => 'https://emis.kemenag.go.id/api/v4/attendance',
            'emis_token' => '',
            'emis_sync_enabled' => '1',
            'backup_retention' => '30',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => str_starts_with($key, 'wa_') ? 'whatsapp' : (str_starts_with($key, 'emis_') ? 'emis' : 'general'),
            ]);
        }
    }

    protected function seedTemplates(): void
    {
        $templates = [
            [
                'key' => 'check_in_ontime',
                'name' => 'Konfirmasi Masuk (Tepat Waktu)',
                'body' => "*KONFIRMASI KEHADIRAN*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\n*{nama_siswa}* ({kelas}) telah hadir di madrasah pada {tanggal} pukul {waktu_scan} WITA dengan status *{status}*.\n\nTerima kasih.\n\n_MAKN Ende - Presensi Digital_",
            ],
            [
                'key' => 'check_in_late',
                'name' => 'Pemberitahuan Terlambat',
                'body' => "*PEMBERITAHUAN PRESENSI*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\nKami informasikan bahwa *{nama_siswa}* ({kelas}) tercatat *TERLAMBAT* masuk madrasah pada {tanggal} pukul {waktu_scan} WITA.\n\nMohon bimbingan dan perhatiannya agar ananda dapat hadir lebih awal. Terima kasih.\n\n_MAKN Ende - Presensi Digital_",
            ],
            [
                'key' => 'check_out',
                'name' => 'Informasi Pulang',
                'body' => "*INFORMASI PULANG*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\n*{nama_siswa}* ({kelas}) telah melakukan presensi pulang pada {tanggal} pukul {waktu_scan} WITA.\n\nTerima kasih atas kerja sama Bapak/Ibu.\n\n_MAKN Ende - Presensi Digital_",
            ],
            [
                'key' => 'late_warning',
                'name' => 'Peringatan Keterlambatan / Alpa',
                'body' => "*PERINGATAN KETERLAMBATAN*\n\nAssalamu'alaikum Bapak/Ibu {nama_wali},\n\nAnanda *{nama_siswa}* ({kelas}) tercatat terlambat/alpa pada {tanggal}. Mohon perhatian dan bimbingannya.\n\n_MAKN Ende - Presensi Digital_",
            ],
        ];

        foreach ($templates as $template) {
            WhatsappTemplate::updateOrCreate(['key' => $template['key']], $template + ['is_active' => true]);
        }
    }

    protected function seedAttendances(): void
    {
        $students = Student::orderBy('id')->get();
        $today = Carbon::today();
        $rows = [];
        $notifications = [];

        for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
            $date = $today->copy()->subDays($dayOffset);

            if ($dayOffset !== 0 && $date->isWeekend()) {
                continue;
            }

            $isToday = $dayOffset === 0;

            foreach ($students as $index => $student) {
                // Hari ini belum semua siswa melakukan scan (mensimulasikan live feed).
                if ($isToday && ($index % 9 === 0)) {
                    continue;
                }

                $roll = random_int(1, 100);

                if ($isToday) {
                    $status = match (true) {
                        $roll <= 82 => Attendance::STATUS_HADIR,
                        $roll <= 88 => Attendance::STATUS_TERLAMBAT,
                        $roll <= 91 => Attendance::STATUS_IZIN,
                        $roll <= 94 => Attendance::STATUS_SAKIT,
                        default => Attendance::STATUS_ALPA,
                    };
                } else {
                    $status = match (true) {
                        $roll <= 87 => Attendance::STATUS_HADIR,
                        $roll <= 93 => Attendance::STATUS_TERLAMBAT,
                        $roll <= 96 => Attendance::STATUS_IZIN,
                        $roll <= 98 => Attendance::STATUS_SAKIT,
                        default => Attendance::STATUS_ALPA,
                    };
                }

                $hasPresence = in_array($status, [Attendance::STATUS_HADIR, Attendance::STATUS_TERLAMBAT], true);
                $timeIn = null;
                $timeOut = null;

                if ($hasPresence) {
                    $minutes = $status === Attendance::STATUS_HADIR
                        ? random_int(390, 434) // 06:30 - 07:14
                        : random_int(436, 480); // 07:16 - 08:00
                    $timeIn = sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);

                    if (! $isToday && random_int(1, 100) <= 92) {
                        $outMinutes = $date->isFriday() ? random_int(660, 690) : random_int(870, 930);
                        $timeOut = sprintf('%02d:%02d:00', intdiv($outMinutes, 60), $outMinutes % 60);
                    }
                }

                $notificationStatus = $hasPresence ? (random_int(1, 100) <= 96 ? 'sent' : 'failed') : 'pending';

                $rows[] = [
                    'student_id' => $student->id,
                    'date' => $date->toDateString(),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => $status,
                    'notification_status' => $notificationStatus,
                    'notification_sent_at' => $notificationStatus === 'sent' ? $date->copy()->setTimeFromTimeString($timeIn ?? '07:00:00') : null,
                    'note' => $status === Attendance::STATUS_IZIN ? 'Izin kegiatan MTQ / dispensasi madrasah' : ($status === Attendance::STATUS_SAKIT ? 'Surat keterangan klinik madrasah' : null),
                    'recorded_by' => null,
                    'created_at' => $date->copy()->setTimeFromTimeString($timeIn ?? '07:00:00'),
                    'updated_at' => $date->copy()->setTimeFromTimeString($timeIn ?? '07:00:00'),
                ];

                if ($isToday && $hasPresence) {
                    $notifications[] = [
                        'student_id' => $student->id,
                        'phone' => $student->parent_phone,
                        'message' => "*KONFIRMASI KEHADIRAN*\n\nAssalamu'alaikum Bapak/Ibu ".$student->parent_name.",\n\n*".$student->name."* telah hadir di madrasah pada ".$date->translatedFormat('d F Y')." pukul ".substr((string) $timeIn, 0, 5)." WITA dengan status *".($status === 'hadir' ? 'Hadir' : 'Terlambat')."*.\n\n_MAKN Ende - Presensi Digital_",
                        'type' => 'check_in',
                        'status' => $notificationStatus,
                        'response' => $notificationStatus === 'sent' ? 'SIMULASI: pesan berhasil diproses tanpa mengirim ke API gateway.' : 'Gateway timeout saat menghubungi server Fonnte.',
                        'sent_at' => $notificationStatus === 'sent' ? $date->copy()->setTimeFromTimeString($timeIn) : null,
                        'created_at' => $date->copy()->setTimeFromTimeString($timeIn),
                        'updated_at' => $date->copy()->setTimeFromTimeString($timeIn),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }

        foreach (array_chunk($notifications, 250) as $chunk) {
            DB::table('whatsapp_logs')->insert($chunk);
        }
    }

    protected function seedWhatsappLogs(): void
    {
        $yesterday = Carbon::yesterday();

        $logs = DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->whereDate('attendances.date', $yesterday->toDateString())
            ->whereNotNull('attendances.time_out')
            ->limit(30)
            ->get()
            ->map(fn ($row) => [
                'student_id' => $row->student_id,
                'phone' => $row->parent_phone,
                'message' => "*INFORMASI PULANG*\n\nAssalamu'alaikum Bapak/Ibu ".$row->parent_name.",\n\n*".$row->name."* telah melakukan presensi pulang pada ".$yesterday->translatedFormat('d F Y')." pukul 15:30 WITA.\n\n_MAKN Ende - Presensi Digital_",
                'type' => 'check_out',
                'status' => 'sent',
                'response' => 'SIMULASI: pesan berhasil diproses tanpa mengirim ke API gateway.',
                'sent_at' => $yesterday->copy()->setTime(15, 30),
                'created_at' => $yesterday->copy()->setTime(15, 30),
                'updated_at' => $yesterday->copy()->setTime(15, 30),
            ])
            ->all();

        if ($logs) {
            DB::table('whatsapp_logs')->insert($logs);
        }
    }

    protected function seedAuditLogs(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $items = [
            ['settings.update', 'Memperbarui jam operasional dan toleransi keterlambatan', 40],
            ['student.create', 'Menambah 8 siswa baru hasil sinkronisasi EMIS 4.0', 180],
            ['class.update', 'Menetapkan Ustazah Fatimah sebagai wali kelas XI TKJ 1', 320],
            ['whatsapp.template', 'Memperbarui template pemberitahuan terlambat', 640],
            ['backup.create', 'Membuat snapshot database harian', 1440],
        ];

        foreach ($items as [$action, $description, $minutesAgo]) {
            DB::table('audit_logs')->insert([
                'user_id' => $admin?->id,
                'action' => $action,
                'description' => $description,
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes($minutesAgo),
            ]);
        }
    }
}
