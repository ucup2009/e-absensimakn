# E-Absensi MAKN Ende

Sistem presensi digital siswa **Madrasah Aliyah Kejuruan Negeri (MAKN) Ende** berbasis **QR Code kartu pelajar** dengan **notifikasi WhatsApp otomatis** ke wali santri. Dibangun dengan Laravel 13, Tailwind CSS 4, dan desain antarmuka “Modern Islamic EdTech” (hijau Islami, Plus Jakarta Sans, Material Symbols).

## Fitur Utama

| Modul | Kemampuan |
| --- | --- |
| Dashboard Admin & Live Monitoring | KPI kehadiran harian, live feed pemindaian, target kehadiran, tren 7 hari, kehadiran per jurusan, statistik WhatsApp |
| Kiosk Scanner QR | Pemindaian kamera (html5-qrcode), input NISN manual, mode masuk/pulang, layar penuh, beep & hasil instan, antrean gerbang |
| Data Presensi & Rekap | Filter periode/kelas/status/pencarian, input manual (izin, sakit, dispensasi), ekspor PDF & Excel (standar EMIS 4.0) |
| Data Master | CRUD siswa (NISN, wali, nomor WA), CRUD rombel + penetapan wali kelas, CRUD pengguna admin/wali kelas |
| Kartu Pelajar | QR unik berbasis NISN, pratinjau kartu CR-80, cetak satuan & massal (per kelas atau siswa terpilih) |
| WhatsApp Gateway | Template pesan dengan tag dinamis, live preview ala WhatsApp, uji kirim, log pengiriman, kirim ulang, mode simulator/Fonnte/Wablas |
| Pengaturan | Jam operasional & toleransi keterlambatan, mode libur, target kehadiran, konfigurasi gateway, EMIS 4.0, queue worker, backup database, audit trail |
| Dashboard Wali Kelas | Ringkasan kelas binaan, presensi harian siswa, tren 7 hari, kirim peringatan WhatsApp massal, ekspor laporan |
| Asisten Analisis | Analisis rule-based lokal: tren kehadiran, siswa terlambat, kelas bermasalah, ringkasan laporan (tanpa API eksternal) |

## Kebutuhan Sistem

- PHP 8.3+ (diuji pada PHP 8.5.12) dengan ekstensi `pdo_mysql`, `gd`, `zip`, `mbstring`, `intl`
- Composer 2.x
- Node.js 20+ dan npm
- MySQL 8.x (default aplikasi ini) atau SQLite (alternatif pengembangan tanpa server database)

## Instalasi

```bash
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
```

Sesuaikan `.env`:

```env
APP_NAME="E-Absensi MAKN Ende"
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
```

Siapkan database dan data demo:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Build aset frontend:

```bash
npm install
npm run build               # atau: npm run dev
```

Jalankan aplikasi:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## Worker Antrean WhatsApp

Notifikasi WhatsApp dikirim secara **asynchronous** melalui queue Laravel, sehingga pemindaian tetap cepat. Jalankan worker:

```bash
php artisan queue:work
```

Tanpa API key gateway, sistem otomatis memakai **mode simulator**: pesan dicatat sebagai terkirim di log tanpa benar-benar dikirim. Untuk pengiriman nyata, isi `Engine = Fonnte` + API key pada menu **Pengaturan → WhatsApp Gateway**.

Perintah uji cepat memproses antrean yang tertunda:

```bash
php artisan queue:work --stop-when-empty
```

## Akun Demo

| Peran | Email | Kata Sandi |
| --- | --- | --- |
| Admin / Operator | `admin@maknende.sch.id` | `password` |
| Wali Kelas (X RPL 1) | `wali01@maknende.sch.id` | `password` |

Wali kelas lain: `wali02@maknende.sch.id` … `wali24@maknende.sch.id` (kata sandi sama).

## Data Demo

Seeder membuat 18 rombel, 486 siswa, 25 pengguna, ±10.600 rekaman presensi 30 hari terakhir, ±400 log WhatsApp, 4 template pesan, 26 pengaturan, dan audit trail.

## Hak Akses

- **Admin / Operator**: seluruh modul (data master, gateway, pengaturan, asisten, rekap semua kelas).
- **Wali Kelas & Guru**: dashboard wali kelas, kiosk scanner, rekap & ekspor kelas binaan, asisten analisis. Akses `/siswa`, `/kelas`, `/pengguna`, `/whatsapp`, `/pengaturan` ditolak (403).

## Catatan Teknis

- Zona waktu aplikasi `Asia/Makassar` (WITA, UTC+8).
- Status presensi ditentukan otomatis: `hadir` jika scan ≤ batas tepat waktu (`check_in_limit`, default 07:15), `terlambat` jika melewati batas.
- Ekspor PDF dibatasi 1.000 baris pertama per dokumen agar respons tetap wajar; gunakan ekspor **Excel** untuk data lengkap.
- Pemindaian memakai `qr_code` kartu (`MAKN-<NISN>`) dan tetap menerima NISN mentah sebagai fallback.
- Kode QR dibuat dengan `simplesoftwareio/simple-qrcode`, PDF dengan `barryvdh/laravel-dompdf`, Excel dengan `phpoffice/phpspreadsheet`.

## Struktur Penting

```
app/
├── Http/Controllers/      # Auth, Dashboard, Scan, Attendance, Student, SchoolClass, User, Whatsapp, Setting, Assistant, WaliKelas
├── Http/Middleware/       # EnsureRole (alias: role)
├── Jobs/                  # SendAttendanceNotificationJob
├── Models/                # User, SchoolClass, Student, Attendance, Setting, WhatsappTemplate, WhatsappLog, AuditLog
└── Services/              # AttendanceService, WhatsAppGatewayService, AttendanceReportService
resources/views/           # layouts, partials, dashboard, scan, attendances, students, classes, users, whatsapp, settings, assistant, wali
database/seeders/          # DatabaseSeeder (data demo)
routes/web.php             # seluruh rute + guard peran
```

## Aplikasi Android (APK)

APK pembungkus WebView tersedia di `android/` (lihat `android/README.md`).

- Halaman unduh di server: **`/download/`** (contoh `https://absensi.maknende.my.id/download/`).
- Berkas APK: `public/download/e-absensi.apk` (versi 1.0.2).
- **Alamat server bisa diganti dari dalam aplikasi** (tombol “Alamat server” di
  halaman masuk, atau “Ganti alamat server” di halaman offline) — tidak perlu
  build ulang saat domain/tunnel berubah.
- Alamat bawaan diatur pada `android/res/values/strings.xml` (`server_url`).
- Build ulang: `.\android\build.ps1 -ServerUrl "https://domain-anda"`.
- Di balik tunnel/reverse proxy, `bootstrap/app.php` memakai
  `trustProxies(at: '*')` agar aset dimuat lewat HTTPS (bukan mixed content).

Catatan:
- Keystore penanda tangan ada di `storage/app/android/e-absensi.jks` (jangan hilang).
- Pemindai kamera di APK hanya aktif bila server memakai **HTTPS**; pada alamat
  `http://` (IP lokal) halaman pindai tetap bisa dipakai lewat input NISN manual.
- iPhone/iPad tidak memakai APK: pasang lewat Safari → Bagikan → **Tambahkan ke Layar Utama** (PWA).

## Lisensi

Dibangun untuk kebutuhan PKL di MAKN Ende — Seven Smarts Indonesia. Laravel bersifat open-source di bawah lisensi MIT.
