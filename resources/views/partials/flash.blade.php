@if (session('success') || session('error') || $errors->any())
    <div class="pointer-events-none fixed right-4 top-4 z-[100] flex w-[min(92vw,380px)] flex-col gap-2">
        @if (session('success'))
            <div data-flash class="pointer-events-auto flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white p-4 shadow-xl shadow-emerald-900/10 animate-rise">
                <span class="material-symbols-outlined text-[22px] text-emerald-600">check_circle</span>
                <div class="flex-1">
                    <p class="text-[13px] font-bold text-slate-800">Berhasil</p>
                    <p class="mt-0.5 text-[12px] leading-relaxed text-slate-500">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="this.closest('[data-flash]').remove()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div data-flash class="pointer-events-auto flex items-start gap-3 rounded-2xl border border-rose-200 bg-white p-4 shadow-xl shadow-rose-900/10 animate-rise">
                <span class="material-symbols-outlined text-[22px] text-rose-600">error</span>
                <div class="flex-1">
                    <p class="text-[13px] font-bold text-slate-800">Gagal</p>
                    <p class="mt-0.5 text-[12px] leading-relaxed text-slate-500">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="this.closest('[data-flash]').remove()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div data-flash class="pointer-events-auto flex items-start gap-3 rounded-2xl border border-amber-200 bg-white p-4 shadow-xl shadow-amber-900/10 animate-rise">
                <span class="material-symbols-outlined text-[22px] text-amber-600">warning</span>
                <div class="flex-1">
                    <p class="text-[13px] font-bold text-slate-800">Periksa kembali data Anda</p>
                    <ul class="mt-1 list-inside list-disc text-[12px] leading-relaxed text-slate-500">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" onclick="this.closest('[data-flash]').remove()" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif
    </div>
@endif
