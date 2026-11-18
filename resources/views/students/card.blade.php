@extends('layouts.app')

@section('title', 'Kartu Pelajar — ' . $student->name)
@section('breadcrumb', 'Siswa / Kartu')

@push('head')
    <style>
        @media print {
            @page { size: A4 portrait; margin: 12mm; }
            .student-card { width: 85.6mm !important; height: 54mm !important; border-radius: 4mm !important; box-shadow: none !important; }
            .print-shell { padding: 0 !important; }
        }
    </style>
@endpush

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('students.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <div>
                    <h2 class="page-title">Kartu Pelajar</h2>
                    <p class="page-subtitle">{{ $student->name }} — {{ $student->schoolClass?->name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('students.edit', $student) }}" class="btn-secondary">
                    <span class="material-symbols-outlined text-[18px]">edit</span> Edit Data
                </a>
                <button type="button" onclick="window.print()" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">print</span> Cetak Kartu
                </button>
            </div>
        </div>

        <div class="print-shell card flex flex-col items-center justify-center gap-4 p-8">
            <div class="w-full max-w-[380px]">
                @include('students._card', ['student' => $student, 'qr' => $qr])
            </div>
            <p class="no-print text-center text-[11px] text-slate-400">
                Ukuran kartu CR-80 (85,6 × 54 mm). Gunakan kertas PVC / art paper 260gsm untuk hasil terbaik.
            </p>
        </div>

        <div class="no-print card-pad">
            <h3 class="text-[13px] font-bold text-ink">Detail Kartu</h3>
            <div class="mt-3 grid gap-3 text-[12px] sm:grid-cols-2">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-slate-400">NISN</span><span class="font-semibold tabular-nums text-slate-700">{{ $student->nisn }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-slate-400">Rombel</span><span class="font-semibold text-slate-700">{{ $student->schoolClass?->name }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-slate-400">Kode QR</span><span class="font-semibold text-slate-700">{{ $student->qrPayload() }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                    <span class="text-slate-400">WhatsApp Wali</span><span class="font-semibold tabular-nums text-slate-700">{{ $student->parent_phone ?: '-' }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
