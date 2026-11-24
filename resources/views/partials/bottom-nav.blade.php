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

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden">
    <div class="mx-auto flex max-w-lg items-stretch gap-1 px-3 py-1">
        @foreach ($items as $item)
            <a href="{{ route($item['route']) }}"
                class="tabbar-item {{ request()->routeIs(...$item['match']) ? 'tabbar-item-active' : '' }}">
                <span class="material-symbols-outlined text-[23px]">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
                <span class="mt-1 h-1 w-1 rounded-full {{ request()->routeIs(...$item['match']) ? 'bg-brand-500' : 'bg-transparent' }}"></span>
            </a>
        @endforeach
    </div>
</nav>
