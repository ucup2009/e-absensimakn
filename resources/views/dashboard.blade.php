@extends('layouts.app')

@section('title', 'Dasbor')
@section('breadcrumb', 'Ringkasan hari ini')

@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $pct = fn ($value) => $recorded > 0 ? round($value / $recorded * 100, 1) : 0;
@endphp

@section('content')
    <div class="mx-auto max-w-[1200px] space-y-4">

        {{-- Sapaan --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-center gap-3">
                @include('partials.avatar', [
                    'user' => auth()->user(),
                    'class' => 'h-11 w-11 rounded-xl lg:h-12 lg:w-12 lg:rounded-2xl',
                    'fallback' => 'bg-brand-500 text-white',
                    'text' => 'text-[15px]',
                ])
                <div class="min-w-0">
                    <h2 class="text-[19px] font-extrabold leading-tight tracking-tight text-ink sm:text-[26px]">
                        {{ $greeting }}, {{ auth()->user()->shortName() }} 👋
                    </h2>
                    <p class="mt-0.5 text-[12px] text-slate-500 sm:mt-1 sm:text-[13px]">
                        {{ $today->locale('id')->translatedFormat('l, d F Y') }} •
                        {{ $notRecorded }} siswa belum absen hari ini.
                    </p>
                </div>
            </div>
            <a href="{{ route('attendances.index') }}" class="btn-secondary hidden sm:inline-flex">
                <span class="material-symbols-outlined text-[19px]">fact_check</span>
                Lihat rekap
            </a>
        </div>

        {{-- Aksi cepat --}}
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-2.5">
            <a href="{{ route('scan.index') }}" class="pressable flex items-center gap-2.5 rounded-2xl bg-brand-500 p-3 text-white sm:flex-col sm:items-start sm:p-3.5">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white/15">
                    <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                </span>
                <span class="text-[12.5px] font-bold leading-tight">Pindai Masuk</span>
            </a>
            <a href="{{ route('scan.index', ['mode' => 'out']) }}" class="pressable flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 sm:flex-col sm:items-start sm:p-3.5 lg:border-white/70 lg:bg-white/70 lg:backdrop-blur-xl">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                </span>
                <span class="text-[12.5px] font-bold leading-tight text-slate-700">Pindai Pulang</span>
            </a>
            <a href="{{ route('attendances.index') }}" class="pressable flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 sm:flex-col sm:items-start sm:p-3.5 lg:border-white/70 lg:bg-white/70 lg:backdrop-blur-xl">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-600">
                    <span class="material-symbols-outlined text-[20px]">fact_check</span>
                </span>
                <span class="text-[12.5px] font-bold leading-tight text-slate-700">Rekap Absen</span>
            </a>
            <a href="{{ route('students.index') }}" class="pressable flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 sm:flex-col sm:items-start sm:p-3.5 lg:border-white/70 lg:bg-white/70 lg:backdrop-blur-xl">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                </span>
                <span class="text-[12.5px] font-bold leading-tight text-slate-700">Data Siswa</span>
            </a>
        </div>

        {{-- Angka utama --}}
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4 lg:gap-2.5">
            <div class="card p-3.5 lg:p-4">
                <p class="text-[11px] font-medium text-slate-500">Hadir</p>
                <p class="mt-0.5 text-[24px] font-extrabold leading-none text-emerald-600 lg:mt-1 lg:text-[28px]">{{ number_format($hadir, 0, ',', '.') }}</p>
                <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-slate-100 lg:mt-3 lg:h-1.5">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pct($hadir) }}%"></div>
                </div>
                <p class="mt-1 text-[10.5px] text-slate-400">{{ $pct($hadir) }}% dari {{ $recorded }} rekaman</p>
            </div>

            <div class="card p-3.5 lg:p-4">
                <p class="text-[11px] font-medium text-slate-500">Terlambat</p>
                <p class="mt-0.5 text-[24px] font-extrabold leading-none text-amber-600 lg:mt-1 lg:text-[28px]">{{ number_format($terlambat, 0, ',', '.') }}</p>
                <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-slate-100 lg:mt-3 lg:h-1.5">
                    <div class="h-full rounded-full bg-amber-400" style="width: {{ $pct($terlambat) }}%"></div>
                </div>
                <p class="mt-1 text-[10.5px] text-slate-400">Batas {{ \App\Models\Setting::get('check_in_limit', '07:15') }} WITA</p>
            </div>

            <div class="card p-3.5 lg:p-4">
                <p class="text-[11px] font-medium text-slate-500">Izin &amp; sakit</p>
                <p class="mt-0.5 text-[24px] font-extrabold leading-none text-sky-600 lg:mt-1 lg:text-[28px]">{{ number_format($izin + $sakit, 0, ',', '.') }}</p>
                <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-slate-100 lg:mt-3 lg:h-1.5">
                    <div class="h-full rounded-full bg-sky-400" style="width: {{ $pct($izin + $sakit) }}%"></div>
                </div>
                <p class="mt-1 text-[10.5px] text-slate-400">Izin {{ $izin }} • sakit {{ $sakit }}</p>
            </div>

            <div class="card p-3.5 lg:p-4">
                <p class="text-[11px] font-medium text-slate-500">Belum absen</p>
                <p class="mt-0.5 text-[24px] font-extrabold leading-none text-rose-600 lg:mt-1 lg:text-[28px]">{{ number_format($alpa + $notRecorded, 0, ',', '.') }}</p>
                <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-slate-100 lg:mt-3 lg:h-1.5">
                    <div class="h-full rounded-full bg-rose-400" style="width: {{ $pct($alpa + $notRecorded) }}%"></div>
                </div>
                <p class="mt-1 text-[10.5px] text-slate-400">{{ $alpa }} alpa • {{ $notRecorded }} belum scan</p>
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3 xl:items-start">
            {{-- Absen terbaru --}}
            <div class="card overflow-hidden xl:col-span-2">
                <div class="flex items-center justify-between gap-3 px-4 pb-2 pt-4 sm:px-5">
                    <div>
                        <h3 class="text-[15px] font-bold text-ink">Absen terbaru</h3>
                        <p class="text-[11px] text-slate-400">Pemindaian hari ini</p>
                    </div>
                    <span class="badge-green">
                        <span class="pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Langsung
                    </span>
                </div>

                <div class="divide-y divide-white/60">
                    @forelse ($recent as $attendance)
                        <div class="flex items-center gap-3 px-4 py-3 sm:px-5">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-brand-50 text-[11px] font-bold text-brand-700">
                                {{ $attendance->student?->initials() }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-slate-700">{{ $attendance->student?->name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $attendance->student?->schoolClass?->name ?? 'Tanpa kelas' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[12px] font-bold tabular-nums text-slate-700">{{ $attendance->time_in ? substr($attendance->time_in, 0, 5) : '-' }}</p>
                                <span class="badge {{ $attendance->statusClasses() }}">{{ $attendance->statusLabel() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300">qr_code_scanner</span>
                            <p class="mt-2 text-sm font-semibold text-slate-500">Belum ada yang absen</p>
                            <p class="text-[11px] text-slate-400">Arahkan siswa ke kiosk untuk memindai kartu.</p>
                            <a href="{{ route('scan.index') }}" class="btn-primary mt-4 w-full sm:w-auto">
                                <span class="material-symbols-outlined text-[19px]">qr_code_scanner</span> Buka Pindai
                            </a>
                        </div>
                    @endforelse
                </div>

                @if ($recent->isNotEmpty())
                    <div class="px-4 pb-4 pt-1 sm:px-5">
                        <a href="{{ route('attendances.index') }}" class="btn-secondary w-full">Lihat semua absen</a>
                    </div>
                @endif
            </div>

            {{-- Ringkasan --}}
            <div class="space-y-4">
                <div class="card hidden p-5 lg:block">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[15px] font-bold text-ink">Kehadiran hari ini</h3>
                        <span class="badge-green">{{ $rate }}%</span>
                    </div>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="relative h-24 w-24 shrink-0 rounded-full"
                            style="background: conic-gradient(#10b981 0 {{ $rate }}%, #e6efea {{ $rate }}% 100%);">
                            <div class="absolute inset-[9px] grid place-items-center rounded-full bg-white/90">
                                <span class="text-lg font-extrabold text-ink">{{ $rate }}%</span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1 space-y-1.5 text-[11px]">
                            <p class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Hadir</span>
                                <span class="font-bold text-slate-700">{{ $hadir }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-amber-400"></span>Terlambat</span>
                                <span class="font-bold text-slate-700">{{ $terlambat }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-sky-400"></span>Izin/sakit</span>
                                <span class="font-bold text-slate-700">{{ $izin + $sakit }}</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-rose-400"></span>Alpa</span>
                                <span class="font-bold text-slate-700">{{ $alpa }}</span>
                            </p>
                        </div>
                    </div>
                    <p class="mt-4 rounded-2xl bg-brand-50/80 px-3.5 py-2.5 text-[11px] leading-relaxed text-brand-800/80">
                        {{ $rate >= 90 ? 'Kehadiran hari ini sudah memenuhi target. Pertahankan!' : 'Kehadiran masih di bawah target. Ajak wali kelas menghubungi siswa.' }}
                    </p>
                </div>

                <div class="card p-5">
                    <h3 class="text-[15px] font-bold text-ink">Tren 7 hari</h3>
                    <p class="text-[11px] text-slate-400">Hadir dan terlambat per hari</p>
                    <div class="mt-3 h-[160px]">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('weeklyChart');
        if (ctx && window.Chart) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($weekly->pluck('label')),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: @json($weekly->pluck('hadir')),
                            backgroundColor: '#10b981',
                            borderRadius: 8,
                            maxBarThickness: 18,
                        },
                        {
                            label: 'Terlambat',
                            data: @json($weekly->pluck('terlambat')),
                            backgroundColor: '#fbbf24',
                            borderRadius: 8,
                            maxBarThickness: 18,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, font: { size: 10, family: 'Plus Jakarta Sans' }, color: '#64748b' },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10, family: 'Plus Jakarta Sans' }, color: '#94a3b8' } },
                        y: { beginAtZero: true, grid: { color: '#eef2f0' }, ticks: { font: { size: 10, family: 'Plus Jakarta Sans' }, color: '#94a3b8', precision: 0 } },
                    },
                },
            });
        }
    </script>
@endpush
