<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi MAKN Ende</title>
    <style>
        @page { margin: 22px 26px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; }
        .header { border-bottom: 3px solid #0d5c3a; padding-bottom: 10px; margin-bottom: 14px; }
        .header table { width: 100%; }
        .header td { vertical-align: middle; }
        .brand { font-size: 15px; font-weight: bold; color: #0d5c3a; letter-spacing: .2px; }
        .sub { font-size: 8.5px; color: #64748b; margin-top: 2px; }
        .title { text-align: right; font-size: 11px; font-weight: bold; color: #0f172a; }
        .title small { display: block; font-weight: normal; font-size: 8px; color: #64748b; margin-top: 3px; }
        .meta { width: 100%; margin-bottom: 10px; }
        .meta td { padding: 2px 0; font-size: 8.5px; color: #475569; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .summary td { background: #f4f7f5; border: 1px solid #e2e8e4; padding: 6px 8px; text-align: center; }
        .summary .label { font-size: 7px; text-transform: uppercase; letter-spacing: .6px; color: #64748b; }
        .summary .value { font-size: 13px; font-weight: bold; color: #0d5c3a; margin-top: 2px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0d5c3a; color: #fff; font-size: 7.5px; text-transform: uppercase; letter-spacing: .4px; padding: 5px 4px; text-align: left; }
        table.data td { border-bottom: 1px solid #e2e8e4; padding: 4.5px 4px; font-size: 8px; }
        .badge { padding: 2px 5px; border-radius: 8px; font-size: 7px; font-weight: bold; }
        .hadir { background: #ecfdf5; color: #047857; }
        .terlambat { background: #fffbeb; color: #b45309; }
        .izin { background: #eff6ff; color: #0369a1; }
        .sakit { background: #f5f3ff; color: #6d28d9; }
        .alpa { background: #fff1f2; color: #be123c; }
        .footer { margin-top: 16px; width: 100%; }
        .footer td { font-size: 8px; color: #64748b; }
        .sign { margin-top: 30px; text-align: right; font-size: 8.5px; }
        .sign .line { margin-top: 34px; border-top: 1px solid #94a3b8; display: inline-block; padding-top: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="brand">MAKN ENDE — MADRASAH ALIYAH KEJURUAN NEGERI</div>
                    <div class="sub">Kementerian Agama Republik Indonesia • Wilayah Flores, Nusa Tenggara Timur</div>
                    <div class="sub">Sistem Presensi Digital &amp; Notifikasi WhatsApp Otomatis</div>
                </td>
                <td style="text-align:right">
                    <div class="title">
                        REKAPITULASI ABSENSI SISWA
                        <small>Dokumen EMIS 4.0 — Kemenag RI</small>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta">
        <tr>
            <td style="width:50%">
                <strong>Periode:</strong> {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}<br>
                <strong>Kelas:</strong> {{ $class?->name ?? 'Seluruh Kelas' }} &nbsp;|&nbsp; <strong>Jurusan:</strong> {{ $class?->department ?? 'Semua Jurusan' }}
            </td>
            <td style="text-align:right">
                <strong>Dicetak oleh:</strong> {{ $printedBy }}<br>
                <strong>Waktu cetak:</strong> {{ now()->timezone('Asia/Makassar')->translatedFormat('d F Y H:i') }} WITA
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td><div class="label">Total Rekaman</div><div class="value">{{ $summary['total'] }}</div></td>
            <td><div class="label">Hadir</div><div class="value">{{ $summary['hadir'] }}</div></td>
            <td><div class="label">Terlambat</div><div class="value">{{ $summary['terlambat'] }}</div></td>
            <td><div class="label">Izin</div><div class="value">{{ $summary['izin'] }}</div></td>
            <td><div class="label">Sakit</div><div class="value">{{ $summary['sakit'] }}</div></td>
            <td><div class="label">Alpa</div><div class="value">{{ $summary['alpa'] }}</div></td>
            <td><div class="label">Kehadiran</div><div class="value">{{ $summary['rate'] }}%</div></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:22px">No</th>
                <th style="width:62px">NISN</th>
                <th>Nama Siswa</th>
                <th style="width:75px">Kelas</th>
                <th style="width:48px">Tanggal</th>
                <th style="width:38px">Masuk</th>
                <th style="width:38px">Pulang</th>
                <th style="width:38px">Durasi</th>
                <th style="width:48px">Status</th>
                <th style="width:55px">Notif WA</th>
                <th style="width:100px">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->student?->nisn }}</td>
                    <td>{{ $attendance->student?->name }}</td>
                    <td>{{ $attendance->student?->schoolClass?->name }}</td>
                    <td>{{ $attendance->date?->format('d/m/Y') }}</td>
                    <td>{{ $attendance->time_in ? substr($attendance->time_in, 0, 5) : '-' }}</td>
                    <td>{{ $attendance->time_out ? substr($attendance->time_out, 0, 5) : '-' }}</td>
                    <td>{{ $attendance->durationLabel() }}</td>
                    <td><span class="badge {{ $attendance->status }}">{{ $attendance->statusLabel() }}</span></td>
                    <td>{{ $attendance->notificationLabel() }}</td>
                    <td>{{ $attendance->note ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center; padding:20px; color:#94a3b8">Tidak ada data pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td>
                Catatan: Rekapitulasi ini dihasilkan otomatis oleh Sistem E-Absensi MAKN Ende.<br>
                Data presensi tersinkronisasi dengan standar pelaporan EMIS 4.0 Kementerian Agama RI.
                @if ($truncated ?? false)
                    <br><strong>Menampilkan maksimal {{ $rowLimit }} baris pertama.</strong> Gunakan ekspor Excel untuk data lengkap.
                @endif
            </td>
            <td style="text-align:right; width:220px">
                <div class="sign">
                    Ende, {{ now()->timezone('Asia/Makassar')->translatedFormat('d F Y') }}<br>
                    Kepala Madrasah,
                    <div class="line">......................................</div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
