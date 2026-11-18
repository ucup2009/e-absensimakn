@extends('layouts.app')

@section('title', 'Cetak Kartu')
@section('breadcrumb', 'Siswa / Cetak Kartu')

@push('head')
    <style>
        @media print {
            @page { size: A4 portrait; margin: 9mm; }
            .print-grid { display: grid !important; grid-template-columns: repeat(2, 85.6mm); gap: 5mm !important; justify-content: center; }
            .student-card { width: 85.6mm !important; height: 54mm !important; border-radius: 3mm !important; box-shadow: none !important; page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
@endpush

@section('content')
    <div class="mx-auto max-w-5xl space-y-5">
        <div class="no-print flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('students.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <div>
                    <h2 class="page-title">Cetak Kartu Massal</h2>
                    <p class="page-subtitle">{{ $students->count() }} kartu siap dicetak (format CR-80).</p>
                </div>
            </div>
            <button type="button" onclick="window.print()" class="btn-primary">
                <span class="material-symbols-outlined text-[18px]">print</span> Cetak {{ $students->count() }} Kartu
            </button>
        </div>

        <div class="no-print rounded-xl bg-brand-50 p-3 text-[11px] leading-relaxed text-brand-800">
            <span class="material-symbols-outlined align-middle text-[15px]">info</span>
            Setiap kartu memuat QR Code unik berbasis NISN. Pastikan pencetakan menggunakan skala 100% tanpa “fit to page”.
        </div>

        @if ($students->isEmpty())
            <div class="card p-12 text-center">
                <span class="material-symbols-outlined text-4xl text-slate-300">print_disabled</span>
                <p class="mt-2 text-sm font-semibold text-slate-500">Tidak ada siswa untuk dicetak</p>
                <p class="text-[11px] text-slate-400">Pilih kelas atau siswa terlebih dahulu.</p>
            </div>
        @else
            <div class="print-grid grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($students as $student)
                    @include('students._card', ['student' => $student])
                @endforeach
            </div>
        @endif
    </div>
@endsection
