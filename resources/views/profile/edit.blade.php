@extends('layouts.app')

@section('title', 'Foto Profil')
@section('breadcrumb', 'Akun / Foto Profil')

@section('content')
    <div class="mx-auto max-w-2xl space-y-5">
        <div class="flex items-center gap-3">
            <a href="{{ $user->isAdmin() ? route('dashboard') : route('wali.dashboard') }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="page-title">Foto Profil</h2>
                <p class="page-subtitle">Foto ini tampil di menu, dasbor, dan daftar pengguna.</p>
            </div>
        </div>

        <div class="card-pad">
            <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">
                <div class="relative">
                    @include('partials.avatar', [
                        'user' => $user,
                        'class' => 'h-28 w-28 rounded-3xl',
                        'fallback' => 'bg-brand-50 text-brand-700',
                        'text' => 'text-3xl',
                    ])
                </div>

                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <p class="text-[16px] font-bold text-ink">{{ $user->name }}</p>
                    <p class="text-[12px] text-slate-500">{{ $user->roleLabel() }} · {{ $user->email }}</p>

                    <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-4 space-y-2.5">
                        @csrf
                        @method('PUT')

                        <label for="photo"
                            class="flex min-h-[52px] cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-brand-300 bg-brand-50/60 px-4 text-[12.5px] font-semibold text-brand-700 transition hover:bg-brand-50">
                            <span class="material-symbols-outlined text-[20px]">add_a_photo</span>
                            Pilih foto dari galeri / kamera
                        </label>
                        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
                            class="hidden" data-photo-input>

                        <p class="text-[11px] leading-relaxed text-slate-400">
                            JPG, PNG, atau WEBP · maksimal 2 MB · sebaiknya foto persegi (1:1).
                        </p>
                        <p data-photo-name class="hidden truncate text-[11px] font-semibold text-brand-700"></p>

                        @error('photo')
                            <p class="text-[11px] font-medium text-rose-600">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="btn-primary w-full">
                            <span class="material-symbols-outlined text-[18px]">upload</span>
                            Simpan Foto
                        </button>
                    </form>

                    @if ($user->photo)
                        <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-2"
                            data-confirm="Hapus foto profil Anda?">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="flex min-h-[44px] w-full items-center justify-center gap-1.5 rounded-xl text-[11.5px] font-semibold text-rose-600 hover:bg-rose-50">
                                <span class="material-symbols-outlined text-[17px]">delete</span>
                                Hapus foto
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid gap-2 border-t border-slate-100 pt-4 text-[12px] sm:grid-cols-2">
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50/80 px-3 py-2.5">
                    <span class="text-slate-400">NIP</span>
                    <span class="font-semibold text-slate-700">{{ $user->nip ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50/80 px-3 py-2.5">
                    <span class="text-slate-400">WhatsApp</span>
                    <span class="font-semibold text-slate-700">{{ $user->phone ?: '—' }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-photo-input]').forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                const label = document.querySelector('[data-photo-name]');
                if (!label) return;
                label.textContent = file ? file.name : '';
                label.classList.toggle('hidden', !file);
            });
        });
    </script>
@endpush
