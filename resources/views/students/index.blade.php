@extends('layouts.app')

@section('title', 'Siswa')
@section('breadcrumb', 'Data')

@php
    $filterActive = filled($filters['q']) || filled($filters['class_id']) || filled($filters['gender']) || filled($filters['qr']);
@endphp

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="badge-green"><span class="material-symbols-outlined text-[13px]">database</span> Master Data</span>
                <p class="mt-1 text-sm text-slate-500">Kelola data siswa, kelas, kartu QR, dan wali kelas.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center">
                <button type="button" data-modal-open="importModal" class="btn-secondary px-2 text-[12px] sm:px-5 sm:text-[14px]">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">upload_file</span>
                    Import EMIS
                </button>
                <a href="{{ route('students.cards', $filters['class_id'] ? ['class_id' => $filters['class_id']] : []) }}" class="btn-secondary px-2 text-[12px] sm:px-5 sm:text-[14px]">
                    <span class="material-symbols-outlined text-[18px]">print</span>
                    Cetak Kartu
                </a>
                <a href="{{ route('students.create') }}" class="btn-primary col-span-2 px-2 text-[12px] sm:col-auto sm:px-5 sm:text-[14px]">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    Tambah Siswa
                </a>
            </div>
        </div>

        @include('partials.master-tabs', ['active' => 'students'])

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Siswa Aktif</p>
                        <p class="mt-1 text-xl font-extrabold text-ink">{{ number_format($stats['total'], 0, ',', '.') }}</p>
                    </div>
                    <div class="stat-icon bg-brand-50 text-brand-600"><span class="material-symbols-outlined text-[20px]">groups</span></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">{{ $stats['male'] }} putra • {{ $stats['female'] }} putri</p>
            </div>
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kartu QR Tercetak</p>
                        <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ number_format($stats['qr'], 0, ',', '.') }}</p>
                    </div>
                    <div class="stat-icon bg-emerald-50 text-emerald-600"><span class="material-symbols-outlined text-[20px]">qr_code_2</span></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">{{ $stats['total'] - $stats['qr'] }} menunggu generate</p>
            </div>
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">WhatsApp Wali</p>
                        <p class="mt-1 text-xl font-extrabold text-sky-600">{{ $stats['total'] > 0 ? round($stats['wa'] / $stats['total'] * 100, 1) : 0 }}%</p>
                    </div>
                    <div class="stat-icon bg-sky-50 text-sky-600"><span class="material-symbols-outlined text-[20px]">perm_phone_msg</span></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">{{ $stats['wa'] }} nomor terhubung</p>
            </div>
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rombongan Belajar</p>
                        <p class="mt-1 text-xl font-extrabold text-violet-600">{{ $stats['classes'] }}</p>
                    </div>
                    <div class="stat-icon bg-violet-50 text-violet-600"><span class="material-symbols-outlined text-[20px]">meeting_room</span></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">EMIS 4.0 • 100% valid</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="card p-4">
            <div class="flex items-center justify-between gap-3 lg:hidden">
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-ink">Filter &amp; pencarian</p>
                    <p class="text-[11px] text-slate-400">{{ $filterActive ? 'Filter sedang dipakai.' : 'Ketuk untuk mencari siswa.' }}</p>
                </div>
                <button type="button" data-filter-toggle aria-expanded="{{ $filterActive ? 'true' : 'false' }}"
                    class="btn-soft min-h-[44px] shrink-0 px-4 text-[12px]">
                    <span class="material-symbols-outlined text-[18px]">tune</span>
                    <span data-filter-toggle-label>{{ $filterActive ? 'Tutup' : 'Buka' }}</span>
                </button>
            </div>

            <div data-filter-panel class="{{ $filterActive ? '' : 'hidden' }} mt-3 lg:mt-0 lg:block">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-12">
                <div class="xl:col-span-3">
                    <label class="label" for="q">Pencarian</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nama, NISN, atau nomor wali…" class="input pl-10">
                    </div>
                </div>
                <div class="xl:col-span-3">
                    <label class="label" for="class_id">Rombel</label>
                    <select id="class_id" name="class_id" class="select">
                        <option value="">Semua rombel</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) $filters['class_id'] === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-2">
                    <label class="label" for="gender">Gender</label>
                    <select id="gender" name="gender" class="select">
                        <option value="">Semua</option>
                        <option value="L" @selected($filters['gender'] === 'L')>Putra</option>
                        <option value="P" @selected($filters['gender'] === 'P')>Putri</option>
                    </select>
                </div>
                <div class="xl:col-span-2">
                    <label class="label" for="qr">Status QR</label>
                    <select id="qr" name="qr" class="select">
                        <option value="">Semua</option>
                        <option value="ready" @selected($filters['qr'] === 'ready')>QR aktif</option>
                        <option value="missing" @selected($filters['qr'] === 'missing')>Belum generate</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 xl:col-span-2">
                    <button type="submit" class="btn-primary flex-1 px-3" title="Terapkan filter"><span class="material-symbols-outlined text-[18px]">filter_alt</span></button>
                    <a href="{{ route('students.index') }}" class="btn-secondary shrink-0 px-3" title="Reset"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
                </div>
            </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-[15px] font-bold text-ink">Direktori Santri</h3>
                    <p class="text-[11px] text-slate-400">Menampilkan {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} dari {{ $students->total() }} siswa</p>
                </div>
                <button type="button" id="print-selected" class="btn-soft py-2 text-[12px]">
                    <span class="material-symbols-outlined text-[17px]">print</span>
                    Cetak Kartu Terpilih
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead class="table-head">
                        <tr>
                            <th class="w-10 px-4 py-2.5"><input type="checkbox" id="check-all" class="h-4 w-4 rounded border-slate-300 text-brand-500"></th>
                            <th class="px-3 py-2.5">Siswa</th>
                            <th class="px-3 py-2.5">NISN</th>
                            <th class="px-3 py-2.5">Rombel</th>
                            <th class="px-3 py-2.5">WhatsApp Wali</th>
                            <th class="px-3 py-2.5">Kartu QR</th>
                            <th class="px-5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($students as $student)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-4 py-3">
                                    <input type="checkbox" value="{{ $student->id }}" class="student-check h-4 w-4 rounded border-slate-300 text-brand-500">
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-[11px] font-bold text-brand-700 ring-1 ring-brand-100">{{ $student->initials() }}</div>
                                        <div class="min-w-0">
                                            <p class="truncate text-[12.5px] font-semibold text-slate-700">{{ $student->name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $student->gender === 'P' ? 'Putri' : 'Putra' }} • {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-[12px] font-medium tabular-nums text-slate-600">{{ $student->nisn }}</td>
                                <td class="px-3 py-3">
                                    <p class="text-[12px] font-medium text-slate-600">{{ $student->schoolClass?->name ?? '-' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $student->schoolClass?->department }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($student->parent_phone)
                                        <p class="text-[12px] font-medium tabular-nums text-slate-600">{{ $student->parent_phone }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $student->parent_name }}</p>
                                    @else
                                        <span class="badge-rose">Belum ada</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @if ($student->qr_code)
                                        <span class="badge-green"><span class="material-symbols-outlined text-[13px]">qr_code_2</span> Aktif</span>
                                    @else
                                        <span class="badge-amber">Belum</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('students.card', $student) }}" title="Kartu pelajar" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600">
                                            <span class="material-symbols-outlined text-[18px]">badge</span>
                                        </a>
                                        <a href="{{ route('students.edit', $student) }}" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" data-confirm="Hapus data {{ $student->name }}? Tindakan ini tidak dapat dibatalkan.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300">group_off</span>
                                    <p class="mt-2 text-sm font-semibold text-slate-500">Tidak ada siswa ditemukan</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">Ubah filter atau tambahkan data siswa baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $students->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Import modal --}}
    <div data-modal id="importModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-ink">Import Data EMIS 4.0</h3>
                    <p class="mt-0.5 text-[12px] text-slate-500">Sinkronisasi data siswa dari Kementerian Agama RI.</p>
                </div>
                <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                <span class="material-symbols-outlined text-3xl text-slate-400">cloud_upload</span>
                <p class="mt-2 text-[12px] font-semibold text-slate-600">Taruh file Excel EMIS di sini</p>
                <p class="mt-1 text-[11px] text-slate-400">Format .xlsx sesuai template EMIS 4.0 Kemenag.</p>
            </div>
            <p class="mt-3 rounded-lg bg-amber-50 p-3 text-[11px] leading-relaxed text-amber-700">
                Fitur import massal memerlukan konfigurasi OAuth2 EMIS pada halaman Pengaturan. Untuk saat ini, gunakan tombol Tambah Siswa.
            </p>
            <button type="button" data-modal-close class="btn-secondary mt-4 w-full">Tutup</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const checks = () => Array.from(document.querySelectorAll('.student-check'));
        const checkAll = document.getElementById('check-all');
        checkAll?.addEventListener('change', () => checks().forEach((checkbox) => (checkbox.checked = checkAll.checked)));

        document.getElementById('print-selected')?.addEventListener('click', () => {
            const ids = checks().filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
            const base = '{{ route('students.cards') }}';
            if (ids.length === 0) {
                window.open(base + '{{ $filters['class_id'] ? '?class_id='.$filters['class_id'] : '' }}', '_blank');
                return;
            }
            const params = ids.map((id) => 'ids[]=' + encodeURIComponent(id)).join('&');
            window.open(base + '?' + params, '_blank');
        });
    </script>
@endpush
