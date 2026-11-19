<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Portal — E-Absensi MAKN Ende</title>
    <link rel="icon" type="image/png" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0d5c3a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="E-Absensi">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
    $logoPng = public_path('images/logo.png');
    $logoSrc = file_exists($logoPng) ? asset('images/logo.png') : asset('images/logo.png');
    @endphp
</head>

<body class="h-full bg-[#F4F5F7]">
    <div class="flex min-h-full items-center justify-center p-0 sm:p-6 lg:p-10">
        <div class="flex min-h-full w-full max-w-[1180px] overflow-hidden bg-white shadow-[0_20px_60px_-28px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/70 sm:min-h-[min(720px,calc(100vh-3rem))] sm:rounded-2xl">

            {{-- Panel identitas lembaga --}}
            <section class="relative hidden w-[46%] shrink-0 overflow-hidden bg-gradient-to-br from-[#0E6743] via-[#0B5A38] to-[#094D30] lg:flex xl:w-[47%]">
                <div class="absolute inset-0 bg-grid opacity-[0.18]"></div>
                <div class="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-emerald-300/10 blur-3xl"></div>
                <div class="absolute -bottom-20 right-0 h-72 w-72 rounded-full bg-emerald-200/10 blur-3xl"></div>

                <div class="relative flex w-full flex-col p-10 xl:p-12">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white/65">Kementerian Agama RI</p>
                        <p class="mt-1.5 text-[15px] font-extrabold tracking-wide text-white">
                            MAKN ENDE <span class="px-0.5 font-normal text-white/45">•</span> FLORES
                        </p>
                    </div>

                    <div class="flex flex-1 items-center justify-center py-10">
                        <div class="rounded-[2rem] bg-white/95 p-4 shadow-[0_28px_60px_-28px_rgba(0,0,0,0.65)] ring-1 ring-white/40">
                            <img src="{{ $logoSrc }}" alt="Logo MAKN Ende"
                                class="w-[210px] max-w-full select-none object-contain xl:w-[240px]">
                        </div>
                    </div>

                    <div>
                        <div class="rounded-xl border border-white/10 bg-[#0A5033]/80 p-5 backdrop-blur-sm">
                            <p class="relative pl-6 text-[11.5px] leading-relaxed text-white/80">
                                <span class="absolute -top-1 left-0 font-serif text-[26px] leading-none text-white/35">“</span>
                                Disiplin, wujudkan generasi santri vokasi yang berdisiplin tinggi, unggul teknologi, dan teguh berakhlak mulia di Bumi Pancasila Ende.
                            </p>
                            <p class="mt-3 flex items-center gap-1.5 text-[10px] font-medium text-white/50">
                                <span class="h-1 w-1 rounded-full bg-emerald-300/80"></span>
                                Civitas Akademika MAKN Ende, NTT
                            </p>
                        </div>

                        <p class="mt-4 text-[11px] font-medium text-white/40">v2.4.0-prod</p>
                    </div>
                </div>
            </section>

            {{-- Panel form --}}
            <section class="flex flex-1 items-center justify-center px-5 py-9 sm:px-10 lg:px-12 xl:px-16">
                <div class="w-full max-w-[520px]">

                    {{-- Brand ringkas (mobile) --}}
                    <div class="mb-7 flex items-center gap-3 lg:hidden">
                        <img src="{{ $logoSrc }}" alt="Logo MAKN Ende" class="h-12 w-12 object-contain">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Kementerian Agama RI</p>
                            <p class="text-sm font-extrabold tracking-wide text-brand-500">MAKN ENDE <span class="font-normal text-slate-400">• FLORES</span></p>
                        </div>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h1 class="text-[28px] font-extrabold tracking-tight text-ink sm:text-[30px]">Masuk/Login</h1>
                            <p class="mt-1.5 text-[13px] text-slate-500">Silakan masukkan kredensial akun terdaftar Anda.</p>
                        </div>
                        <span class="mt-1 grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#EDEAFB] text-[#5B4BB7]">
                            <span class="material-symbols-outlined text-[22px]">verified_user</span>
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-1 rounded-xl bg-[#F1EFFB] p-1.5" data-role-tabs>
                        <button type="button" data-role="admin" data-demo-email="admin@maknende.sch.id"
                            class="flex items-center justify-center gap-2 rounded-lg bg-white px-3 py-2.5 text-[12.5px] font-bold text-brand-500 shadow-sm ring-1 ring-slate-200/60 transition">
                            <span class="material-symbols-outlined text-[17px]">admin_panel_settings</span> Admin / Operator
                        </button>
                        <button type="button" data-role="wali" data-demo-email="wali01@maknende.sch.id"
                            class="flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-[12.5px] font-semibold text-slate-500 transition hover:text-slate-700">
                            <span class="material-symbols-outlined text-[17px]">co_present</span> Wali Kelas &amp; Guru
                        </button>
                    </div>

                    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-[12px] font-bold text-slate-700">Email Madrasah / NIP / Username</label>
                            <div class="relative">
                                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[19px] text-[#8B85AE]">person</span>
                                <input id="email" name="email" type="email" value="{{ old('email', 'admin@maknende.sch.id') }}" required autofocus
                                    autocomplete="username" placeholder="operator@makn-ende.sch.id"
                                    class="block w-full rounded-xl border-0 bg-[#F3F1FB] py-3 pl-11 pr-3.5 text-sm text-slate-700 ring-1 ring-inset ring-[#E6E2F6] transition placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/45 @error('email') ring-rose-300 @enderror">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="mb-1.5 block text-[12px] font-bold text-slate-700">Kata Sandi</label>
                                <a href="#" class="mb-1.5 text-[11px] font-semibold text-brand-500 transition hover:text-brand-600 hover:underline">Lupa Password?</a>
                            </div>
                            <div class="relative">
                                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[19px] text-[#8B85AE]">key</span>
                                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••"
                                    class="block w-full rounded-xl border-0 bg-[#F3F1FB] py-3 pl-11 pr-11 text-sm text-slate-700 ring-1 ring-inset ring-[#E6E2F6] transition placeholder:text-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/45 @error('password') ring-rose-300 @enderror">
                                <button type="button" data-password-toggle aria-label="Tampilkan kata sandi"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition hover:bg-white hover:text-slate-600">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>

                        <label class="flex cursor-pointer items-center gap-2.5 pt-0.5 text-[12px] font-medium text-slate-600">
                            <input type="checkbox" name="remember" class="h-4 w-4 cursor-pointer rounded accent-[#0B5A38] focus:ring-brand-500/30">
                            Ingat sesi saya di komputer ini
                        </label>

                        <button type="submit" class="btn-primary w-full rounded-xl py-3.5 text-[13.5px]">
                            Masuk ke Sistem Presensi
                            <span class="material-symbols-outlined text-[19px]">arrow_forward</span>
                        </button>
                    </form>

                    <div class="mt-5 rounded-xl bg-[#F1EFFB] p-4">
                        <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.1em] text-[#6C63A8]">
                            <span class="material-symbols-outlined text-[15px]">bolt</span> Akses Cepat Pengujian (Staging Demo)
                        </p>
                        <div class="mt-2.5 grid gap-2 sm:grid-cols-2">
                            <button type="button" data-demo-email="admin@maknende.sch.id" data-demo-password="password"
                                class="flex items-center gap-2.5 rounded-lg bg-white/90 px-3 py-2.5 text-left shadow-[0_1px_2px_rgba(16,24,40,0.04)] ring-1 ring-[#E4E0F5] transition hover:bg-white hover:ring-brand-300">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-500">
                                    <span class="material-symbols-outlined text-[16px]">person</span>
                                </span>
                                <span class="text-[11px] font-semibold leading-tight text-slate-700">Ustadz Mansyur<br><span class="font-medium text-slate-400">(Admin Utama)</span></span>
                            </button>
                            <button type="button" data-demo-email="wali01@maknende.sch.id" data-demo-password="password"
                                class="flex items-center gap-2.5 rounded-lg bg-white/90 px-3 py-2.5 text-left shadow-[0_1px_2px_rgba(16,24,40,0.04)] ring-1 ring-[#E4E0F5] transition hover:bg-white hover:ring-brand-300">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-500">
                                    <span class="material-symbols-outlined text-[16px]">person</span>
                                </span>
                                <span class="text-[11px] font-semibold leading-tight text-slate-700">Ustadzah Fatimah<br><span class="font-medium text-slate-400">(Wali Kelas)</span></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-[10.5px] font-medium text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-brand-500">encrypted</span>
                            Argon2id Encrypted <span class="text-slate-300">•</span> Laravel 13
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-brand-500">verified_user</span>
                            reCAPTCHA Enterprise Kemenag
                        </span>
                    </div>

                    <div class="mt-4 flex items-center gap-3 rounded-xl bg-[#F1EFFB] px-3.5 py-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-white/80 text-[#5B4BB7]">
                            <span class="material-symbols-outlined text-[18px]">headset_mic</span>
                        </span>
                        <p class="flex-1 text-[10.5px] leading-snug text-[#6C63A8]">Kendala akun presensi atau lupa PIN?</p>
                        <a href="tel:+6282129987764" class="flex items-center gap-1.5 text-[10.5px] font-bold leading-tight text-slate-700 transition hover:text-brand-600">
                            Hubungi Admin IT<br><span class="font-semibold text-slate-500">(+62 821-2998-7764)</span>
                            <span class="material-symbols-outlined text-[15px] text-slate-400">open_in_new</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @if ($errors->any())
    <div data-flash class="fixed right-4 top-4 z-50 flex w-[min(92vw,380px)] items-start gap-3 rounded-2xl border border-rose-200 bg-white p-4 shadow-xl">
        <span class="material-symbols-outlined text-[22px] text-rose-600">error</span>
        <div class="flex-1">
            <p class="text-[13px] font-bold text-slate-800">Gagal masuk</p>
            <ul class="mt-1 space-y-0.5 text-[12px] text-slate-500">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if (session('success'))
    <div data-flash class="fixed right-4 top-4 z-50 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-[12px] font-medium text-emerald-700 shadow-xl">
        <span class="material-symbols-outlined text-[18px]">check_circle</span> {{ session('success') }}
    </div>
    @endif

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById('password');
                const icon = button.querySelector('span');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.textContent = input.type === 'password' ? 'visibility' : 'visibility_off';
            });
        });

        document.querySelectorAll('[data-demo-email]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('email').value = button.dataset.demoEmail;
                document.getElementById('password').value = button.dataset.demoPassword || 'password';
            });
        });

        document.querySelectorAll('[data-role-tabs] [data-role]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('[data-role-tabs] [data-role]').forEach((tab) => {
                    tab.classList.remove('bg-white', 'text-brand-500', 'shadow-sm', 'ring-1', 'ring-slate-200/60', 'font-bold');
                    tab.classList.add('text-slate-500', 'font-semibold');
                });
                button.classList.add('bg-white', 'text-brand-500', 'shadow-sm', 'ring-1', 'ring-slate-200/60', 'font-bold');
                button.classList.remove('text-slate-500', 'font-semibold');
                document.getElementById('email').value = button.dataset.demoEmail;
                document.getElementById('password').value = 'password';
            });
        });
    </script>
</body>

</html>