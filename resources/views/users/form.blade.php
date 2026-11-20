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

        <form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}"
            enctype="multipart/form-data" class="card-pad space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid gap-3.5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label">Foto Pengguna</label>
                    <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200 bg-white/70 p-3">
                        <div id="photo-preview" class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl bg-slate-100">
                            @include('partials.avatar', [
                                'user' => $user,
                                'class' => 'h-16 w-16 rounded-2xl',
                                'fallback' => 'bg-brand-50 text-brand-700',
                                'text' => 'text-[18px]',
                            ])
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="photo" class="btn-secondary cursor-pointer py-2 text-[11px]">
                                <span class="material-symbols-outlined text-[16px]">photo_camera</span> Pilih foto
                            </label>
                            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="hidden">
                            <p class="mt-1.5 text-[10.5px] leading-relaxed text-slate-400">JPG/PNG/WEBP, maks 2 MB, sebaiknya persegi.</p>
                            @if ($isEdit && $user->photo)
                                <label class="mt-1.5 flex cursor-pointer items-center gap-1.5 text-[10.5px] font-medium text-rose-600">
                                    <input type="checkbox" name="remove_photo" value="1" class="h-3.5 w-3.5 rounded border-rose-300 text-rose-500 focus:ring-rose-500/30">
                                    Hapus foto saat disimpan
                                </label>
                            @endif
                            @error('photo')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

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

@push('scripts')
    <script>
        document.getElementById('photo')?.addEventListener('change', (event) => {
            const file = event.target.files && event.target.files[0];
            const preview = document.getElementById('photo-preview');
            if (!file || !preview) return;

            const url = URL.createObjectURL(file);
            preview.innerHTML = `<img src="${url}" alt="" class="h-full w-full object-cover">`;
        });
    </script>
@endpush
