@extends('layouts.app')

@section('title', 'Dasbor Kelas')
@section('breadcrumb', 'Kelas binaan')

@php
    $totalStudents = $class ? $students->count() : 0;
    $recorded = array_sum($counts ?? []);
    $presentToday = ($counts['hadir'] ?? 0) + ($counts['terlambat'] ?? 0);
    $todayRate = $recorded > 0 ? round($presentToday / $recorded * 100, 1) : 0;
    $monthStart = $date?->copy()->startOfMonth()->toDateString() ?? now()->startOfMonth()->toDateString();
    $exportFilters = ['from' => $monthStart, 'to' => $date?->toDateString() ?? now()->toDateString(), 'class_id' => $class?->id];
@endphp

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex items-center gap-3">
                @include('partials.avatar', [
                    'user' => $class?->homeroomTeacher ?? auth()->user(),
                    'class' => 'h-12 w-12 rounded-2xl',
                    'fallback' => 'bg-emerald-100 text-emerald-700',
                    'text' => 'text-[15px]',
                ])
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge-green"><span class="material-symbols-outlined text-[13px]">co_present</span> Wali Kelas</span>
                        <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">calendar_month</span> {{ $date?->locale('id')->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $class ? 'Wali kelas: '.($class->homeroomTeacher?->name ?? auth()->user()->name).' • '.$totalStudents.' siswa aktif' : 'Anda belum ditetapkan sebagai wali kelas.' }}
                    </p>
                </div>
            </div>

            @if ($class)
                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" class="flex items-center gap-2">
                        <input type="date" name="date" value="{{ $date->toDateString() }}" class="input py-2 text-[12px]">
                        <button type="submit" class="btn-secondary py-2"><span class="material-symbols-outlined text-[17px]">search</span></button>
                    </form>
                    <a href="{{ route('attendances.excel', $exportFilters) }}" class="btn-secondary">
                        <span class="material-symbols-outlined text-[17px] text-emerald-600">table_view</span> Excel
                    </a>
                    <a href="{{ route('attendances.pdf', $exportFilters) }}" class="btn-primary">
                        <span class="material-symbols-outlined text-[17px]">picture_as_pdf</span> PDF
                    </a>
                </div>
            @endif
        </div>

        @if (! $class)
            <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-amber-50 text-amber-500">
                    <span class="material-symbols-outlined text-[38px]">meeting_room</span>
                </div>
                <h3 class="mt-4 text-[16px] font-bold text-ink">Belum ada kelas binaan</h3>
                <p class="mt-1.5 max-w-md text-[12px] leading-relaxed text-slate-500">
                    Hubungi admin madrasah untuk menetapkan Anda sebagai wali kelas pada salah satu rombongan belajar.
                </p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                <div class="card p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Siswa</p>
                    <p class="mt-1 text-xl font-extrabold text-ink">{{ $totalStudents }}</p>
                    <p class="text-[10px] text-slate-400">kelas binaan</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hadir</p>
                    <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ $counts['hadir'] ?? 0 }}</p>
                    <p class="text-[10px] text-emerald-600/70">tepat waktu</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terlambat</p>
                    <p class="mt-1 text-xl font-extrabold text-amber-600">{{ $counts['terlambat'] ?? 0 }}</p>
                    <p class="text-[10px] text-amber-500">perlu pembinaan</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Izin / Sakit</p>
                    <p class="mt-1 text-xl font-extrabold text-sky-600">{{ ($counts['izin'] ?? 0) + ($counts['sakit'] ?? 0) }}</p>
                    <p class="text-[10px] text-slate-400">dispensasi resmi</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alpa / Belum</p>
                    <p class="mt-1 text-xl font-extrabold text-rose-600">{{ ($counts['alpa'] ?? 0) + $notRecorded }}</p>
                    <p class="text-[10px] text-rose-500">tindak lanjut</p>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-3">
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[14px] font-bold text-ink">Kehadiran Hari Ini</h3>
                            <p class="text-[11px] text-slate-400">{{ $date->locale('id')->translatedFormat('d F Y') }}</p>
                        </div>
                        <span class="{{ $todayRate >= 90 ? 'badge-green' : 'badge-amber' }}">{{ $todayRate }}%</span>
                    </div>
                    <div class="mt-4 flex items-center gap-4">
                        <div class="relative h-24 w-24 shrink-0 rounded-full" style="background: conic-gradient(#10b981 0 {{ $todayRate }}%, #e2e8e4 {{ $todayRate }}% 100%);">
                            <div class="absolute inset-2.5 flex flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-lg font-extrabold text-ink">{{ $presentToday }}</span>
                                <span class="text-[9px] text-slate-400">dari {{ $recorded }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5 text-[11px]">
                            <p class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Hadir {{ $counts['hadir'] ?? 0 }}</p>
                            <p class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-amber-400"></span>Terlambat {{ $counts['terlambat'] ?? 0 }}</p>
                            <p class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-sky-400"></span>Izin {{ $counts['izin'] ?? 0 }} • Sakit {{ $counts['sakit'] ?? 0 }}</p>
                            <p class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-rose-400"></span>Alpa {{ $counts['alpa'] ?? 0 }}</p>
                            <p class="flex items-center gap-1.5 text-slate-500"><span class="h-2 w-2 rounded-full bg-slate-300"></span>Belum scan {{ $notRecorded }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-5 xl:col-span-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[14px] font-bold text-ink">Tren 7 Hari • {{ $class->name }}</h3>
                            <p class="text-[11px] text-slate-400">Kehadiran dan keterlambatan harian</p>
                        </div>
                        <span class="badge-slate">Rata-rata bulan {{ $monthRate }}%</span>
                    </div>
                    <div class="mt-3 h-[170px]"><canvas id="waliChart"></canvas></div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-[15px] font-bold text-ink">Daftar Siswa &amp; Presensi</h3>
                        <p class="text-[11px] text-slate-400">{{ $totalStudents }} siswa • klik ekspor untuk laporan bulanan</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <span class="material-symbols-outlined pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-[16px] text-slate-400">search</span>
                            <input id="student-search" placeholder="Cari nama/NISN…" class="input w-44 py-1.5 pl-8 text-[11px]">
                        </div>
                        <form method="POST" action="{{ route('wali.warnings') }}" data-confirm="Kirim peringatan WhatsApp ke siswa terlambat/alpa pada {{ $date->toDateString() }}?">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                            <button type="submit" class="btn-soft py-1.5 text-[11px]">
                                <span class="material-symbols-outlined text-[15px]">campaign</span> Peringatan WA
                            </button>
                        </form>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left" id="student-table">
                        <thead class="table-head">
                            <tr>
                                <th class="px-5 py-2.5">Siswa</th>
                                <th class="px-3 py-2.5">NISN</th>
                                <th class="px-3 py-2.5">Masuk</th>
                                <th class="px-3 py-2.5">Pulang</th>
                                <th class="px-3 py-2.5">Status</th>
                                <th class="px-5 py-2.5 text-right">WhatsApp Wali</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($students as $student)
                                @php($attendance = $student->attendances->first())
                                <tr class="student-row transition hover:bg-slate-50/60">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-[10px] font-bold text-brand-700">{{ $student->initials() }}</div>
                                            <div>
                                                <p class="text-[12.5px] font-semibold text-slate-700 student-name">{{ $student->name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $student->gender === 'P' ? 'Putri' : 'Putra' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-[11px] tabular-nums text-slate-500">{{ $student->nisn }}</td>
                                    <td class="px-3 py-3 text-[12px] font-semibold tabular-nums text-slate-700">{{ $attendance?->time_in ? substr($attendance->time_in, 0, 5) : '-' }}</td>
                                    <td class="px-3 py-3 text-[12px] font-semibold tabular-nums text-slate-700">{{ $attendance?->time_out ? substr($attendance->time_out, 0, 5) : '-' }}</td>
                                    <td class="px-3 py-3">
                                        @if ($attendance)
                                            <span class="badge {{ $attendance->statusClasses() }}">{{ $attendance->statusLabel() }}</span>
                                        @else
                                            <span class="badge-slate">Belum scan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @if ($attendance && $attendance->notification_status === 'sent')
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600"><span class="material-symbols-outlined text-[14px]">done_all</span> Terkirim</span>
                                        @elseif ($attendance && $attendance->notification_status === 'failed')
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600"><span class="material-symbols-outlined text-[14px]">error</span> Gagal</span>
                                        @else
                                            <span class="text-[11px] text-slate-400">{{ $student->parent_phone ?: 'Belum ada nomor' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const search = document.getElementById('student-search');
        search?.addEventListener('input', () => {
            const term = search.value.toLowerCase();
            document.querySelectorAll('.student-row').forEach((row) => {
                row.classList.toggle('hidden', !row.innerText.toLowerCase().includes(term));
            });
        });

        const ctx = document.getElementById('waliChart');
        if (ctx && window.Chart) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json(($weekly ?? collect())->pluck('label')),
                    datasets: [
                        {
                            label: 'Hadir',
                            data: @json(($weekly ?? collect())->pluck('hadir')),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.12)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3,
                        },
                        {
                            label: 'Terlambat',
                            data: @json(($weekly ?? collect())->pluck('terlambat')),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245,158,11,0.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, font: { size: 10 }, color: '#64748b' } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8', precision: 0 } },
                    },
                },
            });
        }
    </script>
@endpush
