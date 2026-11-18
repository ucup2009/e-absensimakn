@extends('layouts.app')

@section('title', 'Analisa Kehadiran')
@section('breadcrumb', 'Analisa')

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="badge-green"><span class="material-symbols-outlined text-[13px]">neurology</span> Analisis lokal</span>
                <p class="mt-1 text-sm text-slate-500">Ringkasan dan saran dari data absen madrasah.</p>
            </div>
            <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">shield</span> Data tidak dikirim ke layanan eksternal</span>
        </div>

        <div class="grid gap-5 xl:grid-cols-5">
            <div class="space-y-5 xl:col-span-2">
                <div class="card grid grid-cols-2 gap-3 p-5">
                    <div class="rounded-xl bg-emerald-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700/70">Tingkat Kehadiran</p>
                        <p class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $insights['rate'] }}%</p>
                        <p class="text-[10px] text-emerald-600/70">bulan berjalan</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700/70">Terlambat</p>
                        <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ $insights['terlambat'] }}</p>
                        <p class="text-[10px] text-amber-600/70">{{ $insights['total'] }} rekaman</p>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[18px] text-brand-500">leaderboard</span>
                        Peringkat Kehadiran Kelas
                    </h3>
                    <p class="mt-0.5 text-[11px] text-slate-400">Periode {{ $insights['from']->translatedFormat('d M') }} – {{ $insights['to']->translatedFormat('d M Y') }}</p>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($insights['classRates'] as $classRate)
                            <div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-semibold text-slate-600">{{ $classRate['name'] }}</span>
                                    <span class="font-bold {{ $classRate['rate'] >= 90 ? 'text-emerald-600' : ($classRate['rate'] >= 75 ? 'text-amber-600' : 'text-rose-600') }}">{{ $classRate['rate'] }}%</span>
                                </div>
                                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $classRate['rate'] >= 90 ? 'bg-emerald-500' : ($classRate['rate'] >= 75 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width: {{ min(100, $classRate['rate']) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-[11px] text-slate-400">Belum ada data kelas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[18px] text-brand-500">person_alert</span>
                        Perlu Perhatian
                    </h3>
                    <div class="mt-3 space-y-2">
                        @forelse ($insights['topLate'] as $student)
                            <div class="flex items-center justify-between rounded-lg border border-slate-100 px-2.5 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-semibold text-slate-600">{{ $student->name }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $student->schoolClass?->name }}</p>
                                </div>
                                <span class="badge-amber">{{ $student->late_count }}×</span>
                            </div>
                        @empty
                            <p class="rounded-lg bg-slate-50 px-3 py-4 text-center text-[10px] text-slate-400">Tidak ada siswa bermasalah bulan ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-5 xl:col-span-3">
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-3 bg-gradient-to-r from-brand-800 to-brand-600 px-5 py-4 text-white">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15"><span class="material-symbols-outlined text-[22px] text-emerald-300">auto_awesome</span></div>
                        <div>
                            <p class="text-[14px] font-bold">Asisten Data Presensi</p>
                            <p class="text-[10px] text-white/60">Rule-based analysis • tidak memakai API eksternal</p>
                        </div>
                        <span class="ml-auto flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2 py-1 text-[10px] font-bold text-emerald-300">
                            <span class="pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Siap
                        </span>
                    </div>

                    <div class="min-h-[320px] bg-slate-50/70 p-5">
                        @if ($answer)
                            <div class="ml-auto max-w-[92%] rounded-2xl rounded-tr-sm bg-white p-4 shadow-sm ring-1 ring-slate-100">
                                <p class="mb-2 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-brand-600">
                                    <span class="material-symbols-outlined text-[14px]">smart_toy</span> Analisis Asisten
                                </p>
                                <p class="whitespace-pre-line text-[12px] leading-relaxed text-slate-600">{{ $answer }}</p>
                            </div>
                        @else
                            <div class="flex h-full min-h-[260px] flex-col items-center justify-center text-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-brand-50 text-brand-500">
                                    <span class="material-symbols-outlined text-[34px]">neurology</span>
                                </div>
                                <p class="mt-3 text-[14px] font-bold text-ink">Apa yang ingin Anda analisis?</p>
                                <p class="mt-1 max-w-sm text-[11px] leading-relaxed text-slate-500">Tanyakan tren kehadiran, siswa terlambat, performa kelas, atau ringkasan untuk laporan.</p>
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('assistant.ask') }}" class="border-t border-slate-100 bg-white p-4">
                        @csrf
                        <div class="mb-2 flex flex-wrap gap-1.5">
                            @foreach (['Bagaimana tren kehadiran bulan ini?', 'Siswa mana yang paling sering terlambat?', 'Kelas mana yang perlu perhatian?', 'Ringkas laporan untuk rapat wali kelas'] as $example)
                                <button type="button" data-prompt="{{ $example }}" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-500 transition hover:border-brand-300 hover:text-brand-600">{{ $example }}</button>
                            @endforeach
                        </div>
                        <div class="flex items-end gap-2">
                            <textarea id="prompt" name="prompt" rows="2" required placeholder="Tulis pertanyaan analisis Anda…"
                                class="textarea resize-none">{{ $prompt }}</textarea>
                            <button type="submit" class="btn-primary shrink-0 px-3.5 py-3">
                                <span class="material-symbols-outlined text-[19px]">send</span>
                            </button>
                        </div>
                        <p class="mt-1.5 text-[9px] text-slate-400">Analisis dihitung dari data presensi madrasah. Bukan pengganti pertimbangan profesional BK.</p>
                    </form>
                </div>

                <div class="card-pad">
                    <h3 class="text-[13px] font-bold text-ink">Ringkasan Cepat</h3>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] sm:grid-cols-4">
                        <div class="rounded-xl bg-slate-50 p-3 text-center"><p class="text-lg font-extrabold text-slate-700">{{ $insights['hadir'] }}</p><p class="text-[9px] text-slate-400">Hadir</p></div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center"><p class="text-lg font-extrabold text-slate-700">{{ $insights['izin'] }}</p><p class="text-[9px] text-slate-400">Izin</p></div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center"><p class="text-lg font-extrabold text-slate-700">{{ $insights['sakit'] }}</p><p class="text-[9px] text-slate-400">Sakit</p></div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center"><p class="text-lg font-extrabold text-slate-700">{{ $insights['alpa'] }}</p><p class="text-[9px] text-slate-400">Alpa</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-prompt]').forEach((button) => {
            button.addEventListener('click', () => {
                const prompt = document.getElementById('prompt');
                prompt.value = button.dataset.prompt;
                prompt.focus();
            });
        });
    </script>
@endpush
