# Firebase / Firestore untuk aplikasi AI Studio

Aplikasi hasil **Google AI Studio (tab Build)** tidak bisa menyambung ke MySQL
Laravel. Jalur yang didukung native: tambahkan **Firebase** ke project AI Studio,
lalu pindahkan datanya ke **Firestore**.

Folder ini berisi alat untuk memindahkan data tersebut.

| Berkas | Fungsi |
| --- | --- |
| `seed.mjs` | Menulis dataset JSON ke Firestore lewat Admin SDK |
| `firestore.rules` | Aturan baca/tulis |
| `firestore.indexes.json` | Index komposit yang dipakai query rekap |
| `firebase.json` | Konfigurasi Firebase CLI |

---

## Langkah 1 — Tambahkan Firebase di AI Studio

1. Buka project aplikasi di [aistudio.google.com](https://aistudio.google.com), tab **Build**.
2. Klik ikon **Firebase** di panel kanan → **Add Firebase / Connect project**.
3. Pilih **Create new Firebase project** (atau pakai yang sudah ada).
4. Saat membuat database, pilih:
   - Mode: **Firestore Native** (bukan Datastore)
   - Lokasi: `asia-southeast2` (Jakarta) — paling dekat, tidak bisa diubah setelah dibuat
5. AI Studio akan menyisipkan `firebase-applet-config.json` ke project. Biarkan apa adanya.

Setelah terhubung, aplikasi AI Studio bisa langsung memakai:

```ts
import { initializeApp } from 'firebase/app';
import { getFirestore, collection, getDocs, query, orderBy } from 'firebase/firestore';
```

## Langkah 2 — Siapkan data

Dari root repo Laravel:

```powershell
php artisan export:dataset
```

Menghasilkan `storage/app/exports/e-absensi-dataset.json`.

## Langkah 3 — Isi Firestore

### Cara A — lewat skrip (disarankan, sekali jalan)

1. Firebase Console → ⚙️ **Project settings** → tab **Service accounts**
   → **Generate new private key**. Simpan sebagai `firebase/service-account.json`.
   File ini **jangan** di-commit.
2. Jalankan:

```powershell
cd firebase
npm install
node seed.mjs --key service-account.json --project <project-id-anda> --dry-run
node seed.mjs --key service-account.json --project <project-id-anda>
```

`--dry-run` dulu untuk melihat jumlah dokumen per koleksi.

### Cara B — lewat AI Studio (tanpa service account)

Kalau tidak mau menyentuh kredensial admin, tempelkan dataset ke prompt AI Studio:

> Berikut dataset JSON aplikasi absensi. Buat satu modul `scripts/seed.ts` yang
> menulis setiap key di `data` menjadi koleksi Firestore dengan nama key yang
> sama, memakai field `id` sebagai document ID. Tampilkan tombol "Isi data awal"
> di halaman Pengaturan yang hanya berjalan kalau koleksi `students` masih kosong.

Cara ini memakai SDK klien yang sudah dikonfigurasi AI Studio, jadi tidak perlu
service account — tetapi tombol seed harus dijalankan manual sekali.

## Langkah 4 — Aturan & index

```powershell
cd firebase
npx firebase-tools deploy --only firestore:rules,firestore:indexes --project <project-id-anda>
```

Kalau belum memakai Firebase Auth, buka `firestore.rules` dan ganti
`allow read: if signedIn();` menjadi `allow read: if true;` **hanya** untuk
prototipe.

## Struktur koleksi

Nama koleksi sama dengan nama tabel, document ID = kolom `id` (kecuali
`settings` yang memakai kolom `key`).

| Koleksi | Isi |
| --- | --- |
| `users` | Admin & wali kelas (tanpa password) |
| `classes` | Rombongan belajar |
| `students` | Data siswa + kartu QR |
| `attendances` | Presensi harian |
| `settings` | Pengaturan key-value |
| `whatsapp_templates` | Template pesan |
| `whatsapp_logs` | Riwayat pengiriman |
| `audit_logs` | Jejak aktivitas |

Relasi:

```
classes.homeroom_teacher_id  -> users.id
students.class_id            -> classes.id
attendances.student_id       -> students.id
attendances.recorded_by      -> users.id
whatsapp_logs.student_id     -> students.id
audit_logs.user_id           -> users.id
```

Nilai enum:

```
users.role                       admin | wali_kelas
students.gender                  L | P
attendances.status               hadir | terlambat | izin | sakit | alpa
attendances.notification_status  pending | sent | failed | skipped
```

## Catatan

- Tanggal dan jam disimpan sebagai **string** (`YYYY-MM-DD`, `HH:MM:SS`) agar
  konsisten dengan MySQL. Kalau ingin memakai tipe Timestamp Firestore, ubah di
  `seed.mjs` pada fungsi `sanitize`.
- Satu dokumen Firestore maksimal 1 MB — data di sini jauh di bawahnya.
- `firebase/service-account.json` sudah masuk `.gitignore`.
