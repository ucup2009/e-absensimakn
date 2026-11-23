# Deploy E-Absensi ke Wasmer Edge

Wasmer Edge menjalankan PHP 8.3 (WebAssembly) + MySQL terkelola. Laravel di
aplikasi ini kompatibel, tetapi ada beberapa penyesuaian yang sudah disiapkan
di repo:

| File | Fungsi |
| --- | --- |
| `wasmer.toml` | Paket PHP 8.3 + dua perintah: `run` (server) dan `artisan` |
| `app.yaml` | Env produksi, database MySQL, volume upload, cron queue |
| `config/php.ini` | Batas upload 8 MB, memori 256 MB |
| `deploy/wasmer-stage.ps1` | Menyalin repo ke folder bersih (tanpa `.env`, `node_modules`, `storage`) |

## 1. Prasyarat

- Akun Wasmer (https://wasmer.io/signup)
- Wasmer CLI: `iwr https://win.wasmer.io -useb | iex`
- Login sekali: `wasmer login`

## 2. Siapkan folder deploy

```powershell
powershell -ExecutionPolicy Bypass -File deploy\wasmer-stage.ps1
```

Hasilnya folder `%TEMP%\e-absensi-wasmer` berisi aplikasi + `vendor` + aset hasil
build, tanpa file rahasia.

## 3. Deploy pertama

```powershell
wasmer deploy --dir "$env:TEMP\e-absensi-wasmer" --owner <akun-anda> --app-name e-absensi
```

Deploy pertama akan membuat database MySQL dan volume `uploads` otomatis.

## 4. Set APP_KEY

```powershell
wasmer app secrets create APP_KEY "base64:...."
```

Ambil nilai dari `.env` lokal (`APP_KEY=`). Setelah menambah secret, deploy ulang.

## 5. Import data

Database baru masih kosong. Isi struktur lewat job `migrate` (otomatis saat
deploy), lalu masukkan data awal:

```powershell
wasmer app database list --with-password
```

Lalu buka **DB Explorer** (Adminer) dari dashboard Wasmer, dan import dump MySQL
dari komputer lokal (`storage/app/backups/*.sql`) di sana.

Akun awal bisa dibuat dengan `php artisan db:seed` (jalankan dari job sementara)
atau insert manual lewat DB Explorer.

## 6. Setelah live

Perbarui `APP_URL` di `app.yaml` menjadi URL asli:
`https://e-absensi-<owner>.wasmer.app` atau domain sendiri, lalu deploy ulang.

## Batasan yang perlu diketahui

- **Satu database per app**, region database hanya `fr-roub1` (Prancis). Artinya
  aplikasi ikut dipin ke region itu; dari Ende latency sekitar 150-250 ms.
- **Filesystem sementara.** Hanya `/app/public/storage` (volume `uploads`) yang
  persisten. Log, cache, dan hasil backup di `storage/app/backups` hilang saat
  instance didaur ulang.
- **Queue tidak punya daemon.** Job `queue-worker` di `app.yaml` menjalankan
  `queue:work --stop-when-empty` tiap menit, jadi notifikasi WhatsApp bisa
  tertunda sampai ~60 detik.
- **PHP CLI server single-thread**, karena itu `scaling.mode: single_concurrency`.
- **Tanpa `storage:link`.** Disk `public` diarahkan ke `public_path('storage')`
  yang di Wasmer di-mount ke volume.
- Paket hanya memuat kode PHP; aset front-end diambil dari `public/build` yang
  sudah di-build (`npm run build`) sebelum staging.
