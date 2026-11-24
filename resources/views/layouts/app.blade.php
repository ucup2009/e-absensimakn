<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dasbor') — E-Absensi MAKN Ende</title>
    <meta name="theme-color" content="#0d5c3a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="E-Absensi">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full">
    {{-- Latar dekoratif untuk efek kaca (hanya desktop) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 hidden overflow-hidden lg:block">
        <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/30 blur-3xl"></div>
        <div class="absolute -right-24 top-1/4 h-80 w-80 rounded-full bg-sky-300/25 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-teal-200/30 blur-3xl"></div>
    </div>

    @include('partials.sidebar')

    <div class="lg:pl-[272px]">
        @include('partials.topbar')

        <main class="mx-auto w-full max-w-[1400px] px-4 pb-24 pt-3.5 sm:px-6 sm:pt-5 lg:px-8 lg:pb-10">
            @yield('content')
        </main>

        <footer class="hidden px-6 pb-8 text-center text-[11px] text-slate-400 lg:block">
            &copy; {{ date('Y') }} MAKN Ende — E-Absensi
        </footer>
    </div>

    @include('partials.bottom-nav')
    @include('partials.flash')
    @stack('scripts')
</body>
</html>
