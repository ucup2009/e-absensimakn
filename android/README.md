# Aplikasi Android E-Absensi

Pembungkus WebView sederhana untuk aplikasi Laravel E-Absensi MAKN Ende.
Isinya hanya `MainActivity` + WebView; seluruh halaman dan data tetap berasal
dari server Laravel.

## Isi folder

| Berkas | Keterangan |
|---|---|
| `AndroidManifest.xml` | Izin internet + kamera, tema, activity utama |
| `java/.../MainActivity.java` | WebView, izin kamera, tombol back, halaman offline |
| `res/values/strings.xml` | **`server_url`** — alamat server yang dibuka aplikasi |
| `res/xml/network_security_config.xml` | Daftar host yang boleh memakai HTTP biasa |
| `assets/offline.html` | Tampilan saat server tidak terjangkau |

## Mengubah alamat server

Edit `res/values/strings.xml`:

```xml
<string name="server_url">http://192.168.2.10:8000</string>
```

- Untuk uji di jaringan madrasah: pakai IP komputer server (`ipconfig`).
- Untuk produksi: pakai domain HTTPS, mis. `https://absensi.maknende.sch.id`.
  Setelah HTTPS aktif, kamera pemindai QR ikut berfungsi (browser/WebView
  memblokir kamera pada alamat `http://` selain localhost).

Jangan lupa tambahkan host tersebut ke `res/xml/network_security_config.xml`
bila masih memakai HTTP.

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
