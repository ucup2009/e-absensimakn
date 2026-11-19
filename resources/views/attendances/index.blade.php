@extends('layouts.app')

@section('title', 'Rekap Absen')
@section('breadcrumb', 'Absensi')

@php
    $exportQuery = http_build_query(array_filter($filters, fn ($value) => $value !== null && $value !== ''));
    $filterActive = filled($filters['q']) || filled($filters['class_id']) || filled($filters['status']);
@endphp

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="badge-green"><span class="material-symbols-outlined text-[13px]">fact_check</span> Rekapitulasi EMIS</span>
                <p class="mt-1 text-sm text-slate-500">Cari, filter, dan unduh data kehadiran siswa.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" data-modal-open="manualAttendance" class="btn-secondary">
                    <span class="material-symbols-outlined text-[18px]">edit_calendar</span>
                    Input Manual
                </button>
                <a href="{{ route('attendances.excel', $filters) }}" class="btn-secondary">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">table_view</span>
                    Export Excel
                </a>
                <a href="{{ route('attendances.pdf', $filters) }}" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    Export PDF
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="card p-4">
            <div class="flex items-center justify-between gap-3 lg:hidden">
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-ink">Filter &amp; pencarian</p>
                    <p class="text-[11px] text-slate-400">{{ $filterActive ? 'Filter sedang dipakai.' : 'Ketuk untuk memilih tanggal atau kelas.' }}</p>
                </div>
                <button type="button" data-filter-toggle aria-expanded="{{ $filterActive ? 'true' : 'false' }}"
                    class="btn-soft min-h-[44px] shrink-0 px-4 text-[12px]">
                    <span class="material-symbols-outlined text-[18px]">tune</span>
                    <span data-filter-toggle-label>{{ $filterActive ? 'Tutup' : 'Buka' }}</span>
                </button>
            </div>

            <div data-filter-panel class="{{ $filterActive ? '' : 'hidden' }} mt-3 lg:mt-0 lg:block">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-12">
                <div class="xl:col-span-2">
                    <label class="label" for="q">Cari siswa</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nama atau NISN…" class="input pl-10">
                    </div>
                </div>
                @if ($isAdmin)
                    <div class="xl:col-span-2">
                        <label class="label" for="class_id">Kelas</label>
                        <select id="class_id" name="class_id" class="select">
                            <option value="">Semua kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) $filters['class_id'] === (string) $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="xl:col-span-2">
                    <label class="label" for="from">Dari tanggal</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="input">
                </div>
                <div class="xl:col-span-2">
                    <label class="label" for="to">Sampai tanggal</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="input">
                </div>
                <div class="xl:col-span-2">
                    <label class="label" for="status">Status</label>
                    <select id="status" name="status" class="select">
                        <option value="">Semua status</option>
                        @foreach (\App\Models\Attendance::statuses() as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 xl:col-span-2">
                    <button type="submit" class="btn-primary flex-1 px-3" title="Terapkan filter">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                    </button>
                    <a href="{{ route('attendances.index') }}" class="btn-secondary shrink-0 px-3" title="Reset">
                        <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    </a>
                </div>
            </div>
            </div>
        </form>

        {{-- Summary --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rekaman</p>
                <p class="mt-1 text-xl font-extrabold text-ink">{{ number_format($summary['total'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400">{{ $filters['from'] }} s/d {{ $filters['to'] }}</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat Kehadiran</p>
                <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ $summary['rate'] }}%</p>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $summary['rate'] }}%"></div>
                </div>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hadir</p>
                <p class="mt-1 text-xl font-extrabold text-slate-700">{{ number_format($summary['hadir'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-emerald-600">tepat waktu</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terlambat</p>
                <p class="mt-1 text-xl font-extrabold text-amber-600">{{ number_format($summary['terlambat'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-amber-500">perlu tindak lanjut</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Izin / Sakit / Alpa</p>
                <p class="mt-1 text-xl font-extrabold text-slate-700">{{ $summary['izin'] }} / {{ $summary['sakit'] }} / {{ $summary['alpa'] }}</p>
                <p class="text-[10px] text-slate-400">{{ $summary['notifications'] }} notifikasi terkirim</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-[15px] font-bold text-ink">Rekap Presensi Siswa</h3>
                    <p class="text-[11px] text-slate-400">Menampilkan {{ $attendances->firstItem() ?? 0 }}–{{ $attendances->lastItem() ?? 0 }} dari {{ $attendances->total() }} rekaman</p>
                </div>
                <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">verified</span> Standar EMIS 4.0</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="table-head">
                        <tr>
                            <th class="px-5 py-2.5">Siswa</th>
                            <th class="px-3 py-2.5">Kelas</th>
                            <th class="px-3 py-2.5">Tanggal</th>
                            <th class="px-3 py-2.5">Masuk</th>
                            <th class="px-3 py-2.5">Pulang</th>
                            <th class="px-3 py-2.5">Durasi</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-3 py-2.5">Notifikasi</th>
                            <th class="px-5 py-2.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attendances as $attendance)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-[10px] font-bold text-brand-700">{{ $attendance->student?->initials() }}</div>
                                        <div class="min-w-0">
                                            <p class="truncate text-[12.5px] font-semibold text-slate-700">{{ $attendance->student?->name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $attendance->student?->nisn }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-[12px] text-slate-600">{{ $attendance->student?->schoolClass?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-[12px] tabular-nums text-slate-600">{{ $attendance->date?->format('d/m/Y') }}</td>
                                <td class="px-3 py-3 text-[12px] font-semibold tabular-nums text-slate-700">{{ $attendance->time_in ? substr($attendance->time_in, 0, 5) : '-' }}</td>
                                <td class="px-3 py-3 text-[12px] font-semibold tabular-nums text-slate-700">{{ $attendance->time_out ? substr($attendance->time_out, 0, 5) : '-' }}</td>
                                <td class="px-3 py-3 text-[12px] text-slate-500">{{ $attendance->durationLabel() }}</td>
                                <td class="px-3 py-3"><span class="badge {{ $attendance->statusClasses() }}">{{ $attendance->statusLabel() }}</span></td>
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $attendance->notification_status === 'sent' ? 'text-emerald-600' : ($attendance->notification_status === 'failed' ? 'text-rose-600' : 'text-slate-400') }}">
                                        <span class="material-symbols-outlined text-[14px]">{{ $attendance->notification_status === 'sent' ? 'done_all' : ($attendance->notification_status === 'failed' ? 'error' : 'schedule') }}</span>
                                        {{ $attendance->notificationLabel() }}
                                    </span>
                                </td>
                                <td class="max-w-[220px] px-5 py-3 text-[11px] text-slate-500">
                                    <span class="line-clamp-1">{{ $attendance->note ?: '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300">event_busy</span>
                                    <p class="mt-2 text-sm font-semibold text-slate-500">Tidak ada data presensi</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">Ubah filter tanggal atau lakukan pemindaian kartu terlebih dahulu.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attendances->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal input manual --}}
    <div data-modal id="manualAttendance" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-ink">Input Presensi Manual</h3>
                    <p class="mt-0.5 text-[12px] text-slate-500">Untuk izin, sakit, dispensasi, atau koreksi data.</p>
                </div>
                <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>

            <form method="POST" action="{{ route('attendances.store') }}" class="mt-4 space-y-3" data-student-form>
                @csrf
                <div>
                    <label class="label" for="student_search">Siswa</label>
                    <div class="relative" data-student-picker
                        data-students="{{ $students->map(fn ($student) => ['id' => $student->id, 'name' => $student->name, 'nisn' => $student->nisn, 'class' => $student->schoolClass?->name])->toJson() }}">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 z-10 -translate-y-1/2 text-[19px] text-slate-400">search</span>
                        <input id="student_search" type="text" data-autofocus autocomplete="off" spellcheck="false"
                            placeholder="Ketik nama atau NISN…" class="input pl-11 pr-11"
                            role="combobox" aria-expanded="false" aria-controls="student_results" aria-autocomplete="list">
                        <input type="hidden" name="student_id" data-student-value>
                        <button type="button" data-student-clear title="Hapus pilihan"
                            class="absolute right-1.5 top-1/2 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[19px]">close</span>
                        </button>
                    </div>
                    <div id="student_results" data-student-results role="listbox"
                        class="mt-2 hidden max-h-56 overflow-y-auto rounded-2xl border border-white/70 bg-white/95 p-1.5 shadow-lg shadow-slate-900/5 backdrop-blur-xl"></div>
                    <p data-student-hint class="mt-1.5 text-[11px] text-slate-400">Ketik nama atau NISN, lalu pilih dari daftar.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="attendance_date">Tanggal</label>
                        <input id="attendance_date" type="date" name="date" value="{{ now()->toDateString() }}" required class="input">
                    </div>
                    <div>
                        <label class="label" for="attendance_status">Status</label>
                        <select id="attendance_status" name="status" required class="select">
                            @foreach (\App\Models\Attendance::statuses() as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'izin')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="time_in">Jam masuk (opsional)</label>
                        <input id="time_in" type="time" name="time_in" class="input">
                    </div>
                    <div>
                        <label class="label" for="time_out">Jam pulang (opsional)</label>
                        <input id="time_out" type="time" name="time_out" class="input">
                    </div>
                </div>
                <div>
                    <label class="label" for="note">Keterangan</label>
                    <textarea id="note" name="note" rows="2" class="textarea" placeholder="Contoh: Surat izin MTQ tingkat provinsi"></textarea>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-[11px] leading-relaxed text-amber-700">
                    Data manual tidak mengirim notifikasi WhatsApp otomatis. Gunakan menu WhatsApp Gateway untuk mengirim pemberitahuan.
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" data-modal-close class="btn-secondary flex-1">Batal</button>
                    <button type="submit" class="btn-primary flex-1">
                        <span class="material-symbols-outlined text-[18px]">save</span> Simpan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
