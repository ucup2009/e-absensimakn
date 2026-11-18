@extends('layouts.app')

@php($isEdit = $user->exists)
@section('title', $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('breadcrumb', 'Data Master / ' . ($isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="page-title">{{ $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
                <p class="page-subtitle">Akun admin memiliki akses penuh, wali kelas hanya melihat kelas binaannya.</p>
            </div>
        </div>

        <form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}" class="card-pad space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid gap-3.5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="name">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Ust. Nama Lengkap, S.Pd."
                        class="input @error('name') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                    @error('name')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="email">Email Madrasah <span class="text-rose-500">*</span></label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="nama@maknende.sch.id"
                        class="input @error('email') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                    @error('email')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="role">Peran <span class="text-rose-500">*</span></label>
                    <select id="role" name="role" required class="select">
                        <option value="wali_kelas" @selected(old('role', $user->role) === 'wali_kelas')>Wali Kelas &amp; Guru</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin / Operator</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="nip">NIP</label>
                    <input id="nip" name="nip" value="{{ old('nip', $user->nip) }}" placeholder="Nomor induk pegawai" class="input tabular-nums">
                </div>
                <div>
                    <label class="label" for="phone">Nomor WhatsApp</label>
                    <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="62812…" inputmode="numeric" class="input tabular-nums">
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="password">Kata Sandi {{ $isEdit ? '(kosongkan jika tidak diubah)' : '*' }}</label>
                    <input id="password" type="text" name="password" {{ $isEdit ? '' : 'required' }} placeholder="Minimal 8 karakter"
                        class="input @error('password') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                    @error('password')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500/30">
                        Akun aktif dan dapat masuk ke sistem
                    </label>
                </div>
            </div>

            <div class="rounded-xl bg-brand-50 p-3 text-[11px] leading-relaxed text-brand-800">
                <span class="material-symbols-outlined align-middle text-[15px]">info</span>
                Penetapan kelas binaan untuk wali kelas dilakukan melalui menu <strong>Rombel / Kelas</strong>.
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
                </button>
            </div>
        </form>
    </div>
@endsection
