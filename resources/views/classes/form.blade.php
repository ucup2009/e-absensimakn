@extends('layouts.app')

@php($isEdit = $class->exists)
@section('title', $isEdit ? 'Ubah Kelas' : 'Tambah Kelas')
@section('breadcrumb', 'Data Master / ' . ($isEdit ? 'Ubah Kelas' : 'Tambah Kelas'))

@section('content')
    <div class="mx-auto max-w-3xl space-y-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('classes.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="page-title">{{ $isEdit ? 'Ubah Kelas' : 'Tambah Rombel Baru' }}</h2>
                <p class="page-subtitle">Lengkapi data rombongan belajar dan tetapkan wali kelas pembina.</p>
            </div>
        </div>

        <form method="POST" action="{{ $isEdit ? route('classes.update', $class) : route('classes.store') }}" class="card-pad space-y-4">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid gap-3.5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="name">Nama Rombel <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $class->name) }}" required placeholder="Contoh: X RPL 1"
                        class="input @error('name') border-rose-300 ring-4 ring-rose-500/10 @enderror">
                    @error('name')<p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="level">Tingkat <span class="text-rose-500">*</span></label>
                    <select id="level" name="level" required class="select">
                        @foreach (['X', 'XI', 'XII'] as $level)
                            <option value="{{ $level }}" @selected(old('level', $class->level) === $level)>Kelas {{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="department">Jurusan <span class="text-rose-500">*</span></label>
                    <select id="department" name="department" required class="select">
                        @foreach (['RPL', 'TKJ', 'ATPH', 'DKV', 'Keagamaan', 'Umum'] as $department)
                            <option value="{{ $department }}" @selected(old('department', $class->department) === $department)>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="homeroom_teacher_id">Wali Kelas</label>
                    <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="select">
                        <option value="">Belum ditetapkan</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('homeroom_teacher_id', $class->homeroom_teacher_id) === (string) $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="academic_year">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <input id="academic_year" name="academic_year" value="{{ old('academic_year', $class->academic_year) }}" required placeholder="2025/2026" class="input">
                </div>
                <div>
                    <label class="label" for="capacity">Kapasitas Siswa</label>
                    <input id="capacity" type="number" name="capacity" value="{{ old('capacity', $class->capacity ?? 36) }}" min="1" max="60" class="input">
                </div>
            </div>

            <div class="rounded-xl bg-brand-50 p-3 text-[11px] leading-relaxed text-brand-800">
                <span class="material-symbols-outlined align-middle text-[15px]">info</span>
                Wali kelas yang ditetapkan akan mendapatkan dashboard khusus untuk memantau kehadiran siswa di rombel ini.
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <a href="{{ route('classes.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Rombel' }}
                </button>
            </div>
        </form>
    </div>
@endsection
