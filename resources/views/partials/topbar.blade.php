@php($user = auth()->user())

<header class="sticky top-0 z-30 border-b border-white/50 bg-white/60 pt-[env(safe-area-inset-top)] backdrop-blur-2xl">
    <div class="mx-auto flex h-[60px] max-w-[1400px] items-center gap-2 px-3.5 sm:px-6 lg:h-[68px] lg:px-8">
        <button type="button" data-sidebar-toggle aria-label="Buka menu"
            class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-white/70 bg-white/70 text-slate-600 backdrop-blur transition hover:bg-white lg:hidden">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>

        <div class="min-w-0 flex-1">
            <p class="hidden text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 sm:block">
                @yield('breadcrumb', 'E-Absensi')
            </p>
            <h1 class="truncate text-[15px] font-extrabold tracking-tight text-ink sm:text-[17px]">
                @yield('title', 'Dasbor')
            </h1>
        </div>

        <div class="hidden items-center gap-2 rounded-2xl border border-white/70 bg-white/60 px-3 py-2 text-[12px] font-medium text-slate-500 backdrop-blur md:flex">
            <span class="material-symbols-outlined text-[16px] text-brand-500">schedule</span>
            <span data-clock class="tabular-nums">--:--</span>
            <span class="text-[10px] font-bold text-slate-400">WITA</span>
        </div>

        <button type="button" data-install-app
            class="hidden min-h-[42px] items-center gap-1.5 rounded-2xl bg-brand-500 px-3.5 text-[12px] font-bold text-white shadow-sm transition hover:bg-brand-600">
            <span class="material-symbols-outlined text-[18px]">install_mobile</span>
            <span class="hidden sm:inline">Pasang aplikasi</span>
        </button>

        <details class="group relative">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-2xl border border-white/70 bg-white/70 py-1.5 pl-1.5 pr-2 backdrop-blur transition hover:bg-white">
                @include('partials.avatar', [
                    'user' => $user,
                    'class' => 'h-9 w-9 rounded-xl',
                    'fallback' => 'bg-brand-500 text-white',
                ])
                <span class="material-symbols-outlined text-[18px] text-slate-400 transition group-open:rotate-180">expand_more</span>
            </summary>
            <div class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-white/70 bg-white/90 p-1.5 shadow-2xl shadow-slate-900/10 backdrop-blur-2xl">
                <div class="flex items-center gap-2.5 px-3 py-2.5">
                    @include('partials.avatar', [
                        'user' => $user,
                        'class' => 'h-10 w-10 rounded-xl',
                        'fallback' => 'bg-brand-500 text-white',
                    ])
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-bold text-ink">{{ $user?->name }}</p>
                        <p class="truncate text-[11px] text-slate-400">{{ $user?->email }}</p>
                    </div>
                </div>
                <div class="my-1 h-px bg-slate-100"></div>
                <a href="{{ route('profile.edit') }}"
                    class="flex min-h-11 items-center gap-2.5 rounded-xl px-3 text-[13px] font-medium text-slate-600 hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[19px]">account_circle</span> Foto profil
                </a>
                <a href="{{ $user?->isAdmin() ? route('dashboard') : route('wali.dashboard') }}"
                    class="flex min-h-11 items-center gap-2.5 rounded-xl px-3 text-[13px] font-medium text-slate-600 hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[19px]">space_dashboard</span> Dasbor
                </a>
                @if ($user?->isAdmin())
                    <a href="{{ route('settings.index') }}"
                        class="flex min-h-11 items-center gap-2.5 rounded-xl px-3 text-[13px] font-medium text-slate-600 hover:bg-slate-50">
                        <span class="material-symbols-outlined text-[19px]">settings</span> Pengaturan
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex min-h-11 w-full items-center gap-2.5 rounded-xl px-3 text-left text-[13px] font-medium text-rose-600 hover:bg-rose-50">
                        <span class="material-symbols-outlined text-[19px]">logout</span> Keluar
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>
