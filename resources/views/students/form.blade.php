@extends('layouts.app')

@php($isEdit = $student->exists)
@section('title', $isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru')
@section('breadcrumb', 'Data Master / ' . ($isEdit ? 'Edit Siswa' : 'Tambah Siswa'))

@section('content')
    <div class="mx-auto max-w-5xl space-y-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('students.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="page-title">{{ $isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru' }}</h2>
                <p class="page-subtitle">{{ $isEdit ? 'Perbarui biodata dan nomor WhatsApp wali santri.' : 'Lengkapi biodata santri dan nomor WhatsApp wali untuk notifikasi otomatis.' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ $isEdit ? route('students.update', $student) : route('students.store') }}" enctype="multipart/form-data" class="grid gap-5 lg:grid-cols-3">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="space-y-5 lg:col-span-2">
                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">badge</span> Identitas Santri
                    </h3>

                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label class="label" for="nisn">NISN <span class="text-rose-500">*</span></label>
                            <input id="nisn" name="nisn" value="{{ old('nisn', $student->nisn) }}" required inputmode="numeric" maxlength="10" placeholder="10 digit NISN"
                                class="input tabular-nums @error('nisn') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                            @error('nisn')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="name">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input id="name" name="name" value="{{ old('name', $student->name) }}" required placeholder="Nama sesuai EMIS"
                                class="input @error('name') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                            @error('name')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="class_id">Rombel / Kelas <span class="text-rose-500">*</span></label>
                            <select id="class_id" name="class_id" required class="select">
                                <option value="">Pilih rombel…</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected((string) old('class_id', $student->class_id) === (string) $class->id)>{{ $class->name }} — {{ $class->department }}</option>
                                @endforeach
                            </select>
                            @error('class_id')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="gender">Jenis Kelamin <span class="text-rose-500">*</span></label>
                            <select id="gender" name="gender" required class="select">
                                <option value="L" @selected(old('gender', $student->gender) === 'L')>Laki-laki / Putra</option>
                                <option value="P" @selected(old('gender', $student->gender) === 'P')>Perempuan / Putri</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="birth_date">Tanggal Lahir</label>
                            <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date?->toDateString()) }}" class="input">
                        </div>
                        <div>
                            <label class="label" for="is_active">Status</label>
                            <label class="flex h-[42px] cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-600">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $student->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-500 focus:ring-brand-500/30">
                                Siswa aktif
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">perm_phone_msg</span> Kontak Wali Santri
                    </h3>
                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label class="label" for="parent_name">Nama Wali / Orang Tua</label>
                            <input id="parent_name" name="parent_name" value="{{ old('parent_name', $student->parent_name) }}" placeholder="Nama orang tua / wali" class="input">
                        </div>
                        <div>
                            <label class="label" for="parent_phone">Nomor WhatsApp Wali</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[12px] font-semibold text-slate-400">+62</span>
                                <input id="parent_phone" name="parent_phone" value="{{ old('parent_phone', preg_replace('/^62/', '', (string) $student->parent_phone)) }}" placeholder="81234567890" inputmode="numeric"
                                    class="input pl-11 tabular-nums @error('parent_phone') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">Nomor akan disimpan dalam format 62… (contoh: 6281234567890).</p>
                            @error('parent_phone')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="address">Alamat</label>
                            <textarea id="address" name="address" rows="2" placeholder="Desa, kecamatan, kabupaten" class="textarea">{{ old('address', $student->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <a href="{{ route('students.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data Siswa' }}
                    </button>
                </div>
            </div>

            {{-- Side preview --}}
            <div class="space-y-5">
                <div class="card-pad">
                    <h3 class="text-[13px] font-bold text-ink">Pratinjau Kartu Pelajar</h3>
                    <p class="mt-0.5 text-[11px] text-slate-400">Format CR-80 siap cetak.</p>
                    <div class="mt-3">
                        @include('students._card', ['student' => $student])
                    </div>
                    @if ($isEdit)
                        <a href="{{ route('students.card', $student) }}" target="_blank" class="btn-secondary mt-3 w-full py-2 text-[12px]">
                            <span class="material-symbols-outlined text-[16px]">print</span> Cetak Kartu
                        </a>
                    @endif
                </div>

                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[13px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[17px] text-brand-500">photo_camera</span> Foto Siswa
                    </h3>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-slate-100 text-slate-400" id="photo-preview">
                            @if ($student->photoUrl())
                                <img src="{{ $student->photoUrl() }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-[28px]">person</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="photo" class="btn-secondary w-full cursor-pointer py-2 text-[11px]">
                                <span class="material-symbols-outlined text-[16px]">upload</span> Pilih foto
                            </label>
                            <input id="photo" name="photo" type="file" accept="image/*" class="hidden">
                            <p class="mt-1 text-[10px] text-slate-400">JPG/PNG, maks 2 MB.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-brand-50 p-4 text-[11px] leading-relaxed text-brand-800">
                    <p class="flex items-center gap-1.5 font-bold"><span class="material-symbols-outlined text-[15px]">info</span> QR Code otomatis</p>
                    <p class="mt-1 text-brand-700/80">Sistem akan membuat QR unik berbasis NISN saat data disimpan. Kartu dapat dicetak langsung dari halaman ini.</p>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('photo')?.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            if (!file) return;
            const preview = document.getElementById('photo-preview');
            const reader = new FileReader();
            reader.onload = () => {
                preview.innerHTML = `<img src="${reader.result}" alt="" class="h-full w-full object-cover">`;
            };
            reader.readAsDataURL(file);
        });
    </script>
@endpush
