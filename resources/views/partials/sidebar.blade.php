@php
    $user = auth()->user();

    $adminMenus = [
        'Menu' => [
            ['label' => 'Dasbor', 'icon' => 'space_dashboard', 'route' => 'dashboard', 'match' => ['dashboard']],
            ['label' => 'Pindai Kartu', 'icon' => 'qr_code_scanner', 'route' => 'scan.index', 'match' => ['scan.*']],
            ['label' => 'Rekap Absen', 'icon' => 'fact_check', 'route' => 'attendances.index', 'match' => ['attendances.*']],
        ],
        'Data' => [
            ['label' => 'Siswa', 'icon' => 'groups', 'route' => 'students.index', 'match' => ['students.*']],
            ['label' => 'Kelas', 'icon' => 'class', 'route' => 'classes.index', 'match' => ['classes.*']],
            ['label' => 'Pengguna', 'icon' => 'badge', 'route' => 'users.index', 'match' => ['users.*']],
        ],
        'Lainnya' => [
            ['label' => 'Pesan WhatsApp', 'icon' => 'chat', 'route' => 'whatsapp.index', 'match' => ['whatsapp.*']],
            ['label' => 'Analisa', 'icon' => 'insights', 'route' => 'assistant.index', 'match' => ['assistant.*']],
            ['label' => 'Pengaturan', 'icon' => 'settings', 'route' => 'settings.index', 'match' => ['settings.*']],
        ],
    ];

    $waliMenus = [
        'Menu' => [
            ['label' => 'Dasbor Kelas', 'icon' => 'space_dashboard', 'route' => 'wali.dashboard', 'match' => ['wali.*']],
            ['label' => 'Pindai Kartu', 'icon' => 'qr_code_scanner', 'route' => 'scan.index', 'match' => ['scan.*']],
            ['label' => 'Rekap Absen', 'icon' => 'fact_check', 'route' => 'attendances.index', 'match' => ['attendances.*']],
        ],
        'Lainnya' => [
            ['label' => 'Analisa', 'icon' => 'insights', 'route' => 'assistant.index', 'match' => ['assistant.*']],
        ],
    ];

    $menus = $user?->isAdmin() ? $adminMenus : $waliMenus;
@endphp

<aside data-sidebar
    class="fixed inset-y-0 left-0 z-50 flex w-[272px] -translate-x-full flex-col overflow-hidden border-r border-white/10 bg-brand-900/85 text-white shadow-[0_24px_60px_-24px_rgba(4,33,22,0.7)] backdrop-blur-2xl transition-transform duration-300 lg:translate-x-0">
    <div class="pointer-events-none absolute inset-0 bg-grid opacity-20"></div>

    <div class="relative flex items-center gap-3 px-5 pb-4 pt-5">
        <div class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white p-1 ring-1 ring-white/30">
            <img src="{{ asset('images/logo.png') }}" alt="Logo MAKN Ende" class="h-full w-full object-contain">
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-[15px] font-extrabold leading-tight">E-Absensi</p>
            <p class="truncate text-[11px] text-white/55">MAKN Ende • Flores</p>
        </div>
        <button type="button" data-sidebar-toggle aria-label="Tutup menu"
            class="grid h-9 w-9 place-items-center rounded-xl text-white/60 transition hover:bg-white/10 hover:text-white lg:hidden">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    <nav class="relative flex-1 space-y-5 overflow-y-auto px-3 pb-4">
        @foreach ($menus as $group => $items)
            <div>
                <p class="mb-1.5 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-white/35">{{ $group }}</p>
                <div class="space-y-1">
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}"
                            class="nav-link {{ request()->routeIs(...$item['match']) ? 'nav-link-active' : '' }}">
                            <span class="material-symbols-outlined text-[21px] {{ request()->routeIs(...$item['match']) ? 'text-emerald-300' : 'text-white/50' }}">{{ $item['icon'] }}</span>
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if (request()->routeIs(...$item['match']))
                                <span class="ml-auto h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="relative border-t border-white/10 p-3">
        <div class="flex items-center gap-3 rounded-2xl bg-white/5 p-2.5">
            <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-emerald-400/20 text-[11px] font-bold text-emerald-100 ring-1 ring-emerald-300/20">
                {{ $user?->initials() }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-[12px] font-semibold">{{ $user?->name }}</p>
                <p class="truncate text-[10px] text-white/50">{{ $user?->roleLabel() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Keluar"
                    class="grid h-9 w-9 place-items-center rounded-xl text-white/50 transition hover:bg-white/10 hover:text-white">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<div data-sidebar-overlay class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-sm lg:hidden"></div>
