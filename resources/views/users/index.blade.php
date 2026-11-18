@extends('layouts.app')

@section('title', 'Pengguna')
@section('breadcrumb', 'Data / Pengguna')

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="badge-green"><span class="material-symbols-outlined text-[13px]">badge</span> Master Pengguna</span>
                <p class="mt-1 text-sm text-slate-500">Kelola akun admin, wali kelas, dan guru.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <span class="material-symbols-outlined text-[18px]">person_add</span> Tambah Pengguna
            </a>
        </div>

        @include('partials.master-tabs', ['active' => 'users'])

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pengguna</p>
                <p class="mt-1 text-xl font-extrabold text-ink">{{ $stats['total'] }}</p>
                <p class="text-[10px] text-slate-400">akun terdaftar</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Admin / Operator</p>
                <p class="mt-1 text-xl font-extrabold text-brand-600">{{ $stats['admins'] }}</p>
                <p class="text-[10px] text-slate-400">akses penuh sistem</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Wali Kelas &amp; Guru</p>
                <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ $stats['teachers'] }}</p>
                <p class="text-[10px] text-slate-400">dashboard terbatas</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Akun Aktif</p>
                <p class="mt-1 text-xl font-extrabold text-sky-600">{{ $stats['active'] }}</p>
                <p class="text-[10px] text-slate-400">dapat masuk sistem</p>
            </div>
        </div>

        <form method="GET" class="card p-4">
            <div class="grid gap-3 sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <label class="label" for="q">Pencarian</label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-slate-400">search</span>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nama, email, atau NIP…" class="input pl-10">
                    </div>
                </div>
                <div>
                    <label class="label" for="role">Peran</label>
                    <select id="role" name="role" class="select">
                        <option value="">Semua peran</option>
                        <option value="admin" @selected($filters['role'] === 'admin')>Admin / Operator</option>
                        <option value="wali_kelas" @selected($filters['role'] === 'wali_kelas')>Wali Kelas &amp; Guru</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary flex-1"><span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary px-3"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
                </div>
            </div>
        </form>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-[15px] font-bold text-ink">Direktori Pengguna</h3>
                    <p class="text-[11px] text-slate-400">Menampilkan {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna</p>
                </div>
                <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">security</span> Role-based access</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left">
                    <thead class="table-head">
                        <tr>
                            <th class="px-5 py-2.5">Pengguna</th>
                            <th class="px-3 py-2.5">Peran</th>
                            <th class="px-3 py-2.5">NIP</th>
                            <th class="px-3 py-2.5">Telepon</th>
                            <th class="px-3 py-2.5">Kelas Binaan</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $user->isAdmin() ? 'bg-brand-500 text-white' : 'bg-emerald-50 text-emerald-700' }} text-[11px] font-bold">
                                            {{ $user->initials() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate text-[12.5px] font-semibold text-slate-700">{{ $user->name }}</p>
                                            <p class="truncate text-[10px] text-slate-400">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="{{ $user->isAdmin() ? 'badge-green' : 'badge-sky' }}">{{ $user->roleLabel() }}</span>
                                </td>
                                <td class="px-3 py-3 text-[12px] tabular-nums text-slate-600">{{ $user->nip ?: '-' }}</td>
                                <td class="px-3 py-3 text-[12px] tabular-nums text-slate-600">{{ $user->phone ?: '-' }}</td>
                                <td class="px-3 py-3 text-[12px] text-slate-600">{{ $user->homeroomClass?->name ?? '—' }}</td>
                                <td class="px-3 py-3">
                                    <span class="{{ $user->is_active ? 'badge-green' : 'badge-rose' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.edit', $user) }}" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        @if (auth()->id() !== $user->id)
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="Hapus akun {{ $user->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-slate-400">Tidak ada pengguna ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection
