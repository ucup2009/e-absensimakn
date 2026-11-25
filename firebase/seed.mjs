// Mengisi Firestore dari berkas `storage/app/exports/e-absensi-dataset.json`.
//
// Pemakaian:
//   cd firebase
//   npm install
//   node seed.mjs --key service-account.json --project e-absensi-xxxx
//
// Kredensial alternatif: set GOOGLE_APPLICATION_CREDENTIALS ke path service
// account, lalu jalankan `node seed.mjs --project e-absensi-xxxx`.
//
// Opsi:
//   --data <path>     Berkas dataset (default: ../storage/app/exports/e-absensi-dataset.json)
//   --key <path>      Service account JSON (default: ./service-account.json)
//   --project <id>    Project ID Firebase (opsional kalau ada di service account)
//   --id-field <nama> Nama kolom yang dipakai sebagai document ID (default: id)
//   --dry-run         Hanya menampilkan rencana, tidak menulis
//   --merge           Pakai set(..., { merge: true }) alih-alih menimpa
//
// Peringatan: tanpa `--merge`, dokumen dengan ID sama akan ditimpa.

import { readFile, access } from 'node:fs/promises';
import { constants } from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const DEFAULT_DATA = path.resolve(import.meta.dirname, '..', 'storage', 'app', 'exports', 'e-absensi-dataset.json');

function parseArgs(argv) {
  const args = { data: DEFAULT_DATA, key: './service-account.json', project: undefined, idField: 'id', dryRun: false, merge: false };

  for (let i = 0; i < argv.length; i += 1) {
    const arg = argv[i];
    if (arg === '--data') args.data = argv[++i];
    else if (arg === '--key') args.key = argv[++i];
    else if (arg === '--project') args.project = argv[++i];
    else if (arg === '--id-field') args.idField = argv[++i];
    else if (arg === '--dry-run') args.dryRun = true;
    else if (arg === '--merge') args.merge = true;
    else if (arg === '--help' || arg === '-h') {
      console.log('Lihat komentar di bagian atas seed.mjs.');
      process.exit(0);
    } else {
      console.error(`Argumen tidak dikenal: ${arg}`);
      process.exit(1);
    }
  }

  return args;
}

async function exists(file) {
  try {
    await access(file, constants.R_OK);
    return true;
  } catch {
    return false;
  }
}

/**
 * Firestore tidak menerima `undefined`, dan angka besar/infinity bermasalah.
 * Semua nilai dirapikan jadi tipe yang aman.
 */
function sanitize(value) {
  if (value === undefined || value === null) return null;
  if (typeof value === 'number') return Number.isFinite(value) ? value : null;
  if (Array.isArray(value)) return value.map(sanitize);
  if (typeof value === 'object') {
    const out = {};
    for (const [k, v] of Object.entries(value)) out[k] = sanitize(v);
    return out;
  }
  return value;
}

function documentId(row, idField) {
  if (row[idField] !== undefined && row[idField] !== null && row[idField] !== '') return String(row[idField]);
  // Tabel settings punya kolom `key` yang lebih enak dibaca sebagai ID.
  if (row.key !== undefined && row.key !== null && row.key !== '') return String(row.key);
  return undefined;
}

const args = parseArgs(process.argv.slice(2));

if (!(await exists(args.data))) {
  console.error(`Dataset tidak ditemukan: ${args.data}`);
  console.error('Jalankan dulu:  php artisan export:dataset');
  process.exit(1);
}

const dataset = JSON.parse(await readFile(args.data, 'utf8'));
const collections = Object.entries(dataset.data ?? {});

if (collections.length === 0) {
  console.error('Dataset tidak berisi tabel apa pun.');
  process.exit(1);
}

const initOptions = {};

if (args.project) initOptions.projectId = args.project;

let credentialSource = null;
let serviceAccount = null;

if (await exists(args.key)) {
  serviceAccount = JSON.parse(await readFile(args.key, 'utf8'));
  initOptions.projectId = args.project ?? serviceAccount.project_id;
  credentialSource = path.resolve(args.key);
} else if (process.env.GOOGLE_APPLICATION_CREDENTIALS) {
  credentialSource = 'GOOGLE_APPLICATION_CREDENTIALS';
}

const totalDocs = collections.reduce((sum, [, rows]) => sum + rows.length, 0);

console.log(`Dataset    : ${path.relative(process.cwd(), args.data)}`);
console.log(`Project    : ${initOptions.projectId ?? '(belum ditentukan)'}`);
console.log(`Kredensial : ${credentialSource ?? '(tidak ada)'}`);
console.log(`Koleksi    : ${collections.length} (${totalDocs} dokumen)`);
collections.forEach(([name, rows]) => console.log(`  - ${name.padEnd(20)} ${rows.length}`));

if (args.dryRun) {
  console.log('\n--dry-run aktif: tidak ada yang ditulis.');
  process.exit(0);
}

if (!credentialSource) {
  console.error('\nTidak ada kredensial.');
  console.error(`  - letakkan service account di ${path.resolve(args.key)}, atau`);
  console.error('  - set environment variable GOOGLE_APPLICATION_CREDENTIALS');
  process.exit(1);
}

if (!initOptions.projectId) {
  console.error('Project ID tidak diketahui. Tambahkan --project <id-firebase>.');
  process.exit(1);
}

let initializeApp;
let getFirestore;
let applicationDefault;
let cert;

try {
  ({ initializeApp, applicationDefault, cert } = await import('firebase-admin/app'));
  ({ getFirestore } = await import('firebase-admin/firestore'));
} catch {
  console.error('\nPaket firebase-admin belum terpasang.');
  console.error('Jalankan dulu:  cd firebase; npm install');
  process.exit(1);
}

initOptions.credential = serviceAccount ? cert(serviceAccount) : applicationDefault();

initializeApp(initOptions);

const db = getFirestore();

for (const [name, rows] of collections) {
  let written = 0;

  for (let start = 0; start < rows.length; start += 400) {
    const slice = rows.slice(start, start + 400);
    const batch = db.batch();

    for (const row of slice) {
      const docId = documentId(row, args.idField);
      const ref = docId ? db.collection(name).doc(docId) : db.collection(name).doc();
      const payload = sanitize(row);

      if (args.merge) batch.set(ref, payload, { merge: true });
      else batch.set(ref, payload);
    }

    await batch.commit();
    written += slice.length;
  }

  console.log(`  tulis ${name.padEnd(20)} ${written} dokumen`);
}

console.log('\nSelesai. Buka Firebase Console → Firestore untuk memeriksa data.');
