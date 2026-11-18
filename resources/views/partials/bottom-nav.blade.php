@php
    $user = auth()->user();

    $items = $user?->isAdmin()
        ? [
            ['label' => 'Dasbor', 'icon' => 'space_dashboard', 'route' => 'dashboard', 'match' => ['dashboard']],
            ['label' => 'Pindai', 'icon' => 'qr_code_scanner', 'route' => 'scan.index', 'match' => ['scan.*']],
            ['label' => 'Rekap', 'icon' => 'fact_check', 'route' => 'attendances.index', 'match' => ['attendances.*']],
            ['label' => 'Siswa', 'icon' => 'groups', 'route' => 'students.index', 'match' => ['students.*', 'classes.*', 'users.*']],
        ]
        : [
            ['label' => 'Dasbor', 'icon' => 'space_dashboard', 'route' => 'wali.dashboard', 'match' => ['wali.*']],
            ['label' => 'Pindai', 'icon' => 'qr_code_scanner', 'route' => 'scan.index', 'match' => ['scan.*']],
            ['label' => 'Rekap', 'icon' => 'fact_check', 'route' => 'attendances.index', 'match' => ['attendances.*']],
            ['label' => 'Analisa', 'icon' => 'insights', 'route' => 'assistant.index', 'match' => ['assistant.*']],
        ];
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-white/60 bg-white/75 pb-[env(safe-area-inset-bottom)] shadow-[0_-10px_30px_-18px_rgba(15,23,42,0.35)] backdrop-blur-2xl lg:hidden">
    <div class="mx-auto flex max-w-lg items-stretch gap-1 px-2 py-1.5">
        @foreach ($items as $item)
            <a href="{{ route($item['route']) }}"
                class="tabbar-item {{ request()->routeIs(...$item['match']) ? 'tabbar-item-active' : '' }}">
                <span class="material-symbols-outlined text-[23px]">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <button type="button" data-sidebar-toggle class="tabbar-item">
            <span class="material-symbols-outlined text-[23px]">menu</span>
            <span>Menu</span>
        </button>
    </div>
</nav>
