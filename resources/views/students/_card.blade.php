@php
    $payload = $student->qrPayload();
    $qrSvg = $qr ?? ($payload ? \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(180)->margin(1)->generate($payload) : null);
    $school = \App\Models\Setting::get('school_name', 'MAKN Ende');
@endphp

<div class="student-card relative overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-slate-900/10">
    <div class="absolute inset-x-0 top-0 h-[34%] bg-gradient-to-r from-brand-800 via-brand-700 to-brand-500"></div>
    <div class="absolute right-3 top-2.5 flex items-center gap-1.5 text-white/80">
        <span class="material-symbols-outlined text-[13px] text-emerald-300">school</span>
        <span class="text-[7px] font-bold uppercase tracking-[0.14em]">{{ $school }}</span>
    </div>

    <div class="relative flex h-full flex-col px-3.5 pb-3 pt-5">
        <div class="flex items-center gap-2.5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 border-white/70 bg-brand-50 text-[13px] font-extrabold text-brand-700 shadow-md">
                @if ($student->photoUrl())
                    <img src="{{ $student->photoUrl() }}" alt="{{ $student->name }}" class="h-full w-full object-cover">
                @else
                    {{ $student->initials() }}
                @endif
            </div>
            <div class="min-w-0 text-white">
                <p class="text-[7px] font-semibold uppercase tracking-[0.16em] text-emerald-200/90">Kartu Pelajar</p>
                <p class="mt-0.5 truncate text-[11px] font-bold leading-tight">{{ $student->name }}</p>
                <p class="text-[7.5px] text-white/70">{{ $student->schoolClass?->name }} • {{ $student->schoolClass?->department }}</p>
            </div>
        </div>

        <div class="mt-auto flex items-end justify-between gap-2">
            <div class="min-w-0 flex-1 space-y-1">
                <div>
                    <p class="text-[6px] font-bold uppercase tracking-wider text-slate-400">NISN</p>
                    <p class="text-[10px] font-extrabold tabular-nums text-slate-700">{{ $student->nisn }}</p>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    <div>
                        <p class="text-[6px] font-bold uppercase tracking-wider text-slate-400">Rombel</p>
                        <p class="truncate text-[7.5px] font-semibold text-slate-600">{{ $student->schoolClass?->name }}</p>
                    </div>
                    <div>
                        <p class="text-[6px] font-bold uppercase tracking-wider text-slate-400">Gender</p>
                        <p class="text-[7.5px] font-semibold text-slate-600">{{ $student->gender === 'P' ? 'Putri' : 'Putra' }}</p>
                    </div>
                </div>
                <p class="truncate text-[6px] text-slate-400">Wali: {{ $student->parent_name ?: '-' }}</p>
            </div>
            <div class="shrink-0 rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                <div class="flex h-[46px] w-[46px] items-center justify-center [&>svg]:h-full [&>svg]:w-full">
                    @if ($qrSvg)
                        {!! $qrSvg !!}
                    @else
                        <span class="material-symbols-outlined text-[26px] text-slate-300">qr_code_2</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-2 flex items-center justify-between border-t border-dashed border-slate-200 pt-1.5">
            <p class="text-[5.5px] font-semibold uppercase tracking-wider text-slate-400">Presensi Digital • QR Terenkripsi</p>
            <p class="text-[5.5px] text-slate-300">MAKN Ende</p>
        </div>
    </div>
</div>
