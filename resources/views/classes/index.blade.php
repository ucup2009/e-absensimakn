@extends('layouts.app')

@section('title', 'Kelas')
@section('breadcrumb', 'Data / Kelas')

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="badge-green"><span class="material-symbols-outlined text-[13px]">meeting_room</span> Master Kelas</span>
                <p class="mt-1 text-sm text-slate-500">Atur kelas, jurusan, dan wali kelas.</p>
            </div>
            <a href="{{ route('classes.create') }}" class="btn-primary">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Kelas
            </a>
        </div>

        @include('partials.master-tabs', ['active' => 'classes'])

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Rombel</p>
                <p class="mt-1 text-xl font-extrabold text-ink">{{ $stats['classes'] }}</p>
                <p class="text-[10px] text-slate-400">{{ $stats['departments'] }} jurusan</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Siswa Aktif</p>
                <p class="mt-1 text-xl font-extrabold text-brand-600">{{ number_format($stats['students'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400">tersebar di seluruh rombel</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Wali Kelas Ditunjuk</p>
                <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ $stats['withHomeroom'] }}/{{ $stats['classes'] }}</p>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $stats['classes'] > 0 ? round($stats['withHomeroom'] / $stats['classes'] * 100) : 0 }}%"></div>
                </div>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Belum Ada Wali</p>
                <p class="mt-1 text-xl font-extrabold text-amber-600">{{ $stats['classes'] - $stats['withHomeroom'] }}</p>
                <p class="text-[10px] text-slate-400">segera tetapkan pembina</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-[15px] font-bold text-ink">Daftar Rombel</h3>
                    <p class="text-[11px] text-slate-400">Tahun ajaran {{ \App\Models\Setting::get('academic_year', '2025/2026') }}</p>
                </div>
                <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">verified</span> EMIS 4.0</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left">
                    <thead class="table-head">
                        <tr>
                            <th class="px-5 py-2.5">Rombel</th>
                            <th class="px-3 py-2.5">Tingkat</th>
                            <th class="px-3 py-2.5">Jurusan</th>
                            <th class="px-3 py-2.5">Wali Kelas</th>
                            <th class="px-3 py-2.5">Siswa</th>
                            <th class="px-3 py-2.5">Kapasitas</th>
                            <th class="px-5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($classes as $class)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-[10px] font-extrabold text-brand-700">{{ $class->level }}</div>
                                        <div>
                                            <p class="text-[13px] font-semibold text-slate-700">{{ $class->name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $class->academic_year }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-[12px] text-slate-600">Kelas {{ $class->level }}</td>
                                <td class="px-3 py-3"><span class="badge-sky">{{ $class->department }}</span></td>
                                <td class="px-3 py-3">
                                    @if ($class->homeroomTeacher)
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-[9px] font-bold text-emerald-700">{{ $class->homeroomTeacher->initials() }}</div>
                                            <div>
                                                <p class="text-[12px] font-medium text-slate-600">{{ $class->homeroomTeacher->name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $class->homeroomTeacher->email }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge-amber">Belum ditetapkan</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-[12px] font-semibold text-slate-700">{{ $class->students_count }} siswa</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-brand-500" style="width: {{ min(100, round($class->students_count / max(1, $class->capacity) * 100)) }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-slate-400">{{ $class->students_count }}/{{ $class->capacity }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('students.index', ['class_id' => $class->id]) }}" title="Lihat siswa" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                            <span class="material-symbols-outlined text-[18px]">groups</span>
                                        </a>
                                        <a href="{{ route('classes.edit', $class) }}" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form method="POST" action="{{ route('classes.destroy', $class) }}" data-confirm="Hapus kelas {{ $class->name }}? Siswa pada kelas ini akan menjadi tanpa kelas.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-slate-400">Belum ada rombel.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($classes->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $classes->links() }}</div>
            @endif
        </div>
    </div>
@endsection
