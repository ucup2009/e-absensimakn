# Aplikasi Android E-Absensi

Pembungkus WebView sederhana untuk aplikasi Laravel E-Absensi MAKN Ende.
Isinya hanya `MainActivity` + WebView; seluruh halaman dan data tetap berasal
dari server Laravel.

## Isi folder

| Berkas | Keterangan |
|---|---|
| `AndroidManifest.xml` | Izin internet + kamera, tema, activity utama |
| `java/.../MainActivity.java` | WebView, izin kamera, tombol back, halaman offline |
| `res/values/strings.xml` | **`server_url`** — alamat server bawaan (dipakai pertama kali) |
| `res/mipmap-*` | Ikon launcher tiap kerapatan + lapisan adaptive icon |
| `res/xml/network_security_config.xml` | Mengizinkan HTTP biasa (IP lokal / tunnel) |
| `assets/offline.html` | Tampilan saat server tidak terjangkau + tombol ganti alamat |

## Ikon aplikasi

Ikon dibuat dari `public/images/logo.png` oleh `make-icons.ps1`:

```powershell
powershell -ExecutionPolicy Bypass -File android\make-icons.ps1
```

- Ikon biasa & bulat: `48 / 72 / 96 / 144 / 192 px`.
- Adaptive icon (Android 8+): lapisan depan `108dp` dengan logo di zona aman
  (46 % kanvas) sehingga tidak terpotong oleh mask bulat/squircle.
- Logo tidak diregangkan: rasio asli dipertahankan, margin transparan dipangkas.

## Mengubah alamat server

Ada dua cara:

1. **Dari dalam aplikasi (tanpa build ulang)** — versi 1.0.1 ke atas:
   - Di halaman masuk, ketuk tombol **“Alamat server: …”** di bawah form login.
   - Atau, saat muncul halaman *Server tidak terjangkau*, ketuk
     **Ganti alamat server**.
   - Alamat disimpan di HP. Tombol **Bawaan** mengembalikan ke alamat pabrik
     dari `strings.xml`.
2. **Alamat bawaan (dipasang baru)** — edit `res/values/strings.xml`:

```xml
<string name="server_url">https://absensi.maknende.my.id</string>
```

- Untuk uji di jaringan madrasah: pakai IP komputer server (`ipconfig`),
  mis. `http://192.168.2.10:8000`.
- Untuk kamera pemindai QR: server **wajib HTTPS** (WebView memblokir kamera
  pada `http://` selain localhost). Contoh gratis: Cloudflare Quick Tunnel.

## HTTPS gratis dengan Cloudflare Quick Tunnel

```powershell
cloudflared tunnel --url http://localhost:8000
```

Contoh keluaran: `https://kata-kata-acak.trycloudflare.com` → masukkan alamat
itu lewat menu **Alamat server** di aplikasi. URL berubah setiap tunnel
dijalankan ulang, tetapi APK tidak perlu di-build ulang lagi.

Agar Laravel menghasilkan URL aset `https://` di balik tunnel, aplikasi
memakai `$middleware->trustProxies(at: '*')` di `bootstrap/app.php`.

## Membangun ulang APK

Butuh: JDK 17+ dan Android build-tools + platform (tanpa Android Studio).

```powershell
# 1. Unduh alat (sekali saja, ± 120 MB)
#    build-tools: https://dl.google.com/android/repository/build-tools_r34-windows.zip
#    platform 34: https://dl.google.com/android/repository/platform-34-ext7_r03.zip

# 2. Build
.\android\build.ps1
```

Hasil: `android/build/e-absensi.apk`.

## Catatan penting

- **Keystore** (`storage/app/android/e-absensi.jks`) adalah kunci tanda tangan.
  Simpan baik-baik; tanpa file ini APK versi berikutnya tidak bisa memperbarui
  aplikasi yang sudah terpasang.
- APK ini belum masuk Play Store. Untuk Play Store diperlukan akun developer
  Google dan berkas `.aab`, bukan `.apk`.
- Untuk iOS diperlukan macOS + Xcode; tidak bisa dibangun dari Windows.
