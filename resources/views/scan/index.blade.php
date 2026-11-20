@extends('layouts.app')

@section('title', 'Pindai Kartu')
@section('breadcrumb', 'Pindai')

@php
    $initialMode = request('mode') === 'out' ? 'out' : 'in';
@endphp

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5" id="kiosk">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-green"><span class="pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Kamera siap</span>
                    <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">router</span> Gerbang Putra • Kiosk 01</span>
                    <span class="badge-sky"><span class="material-symbols-outlined text-[13px]">schedule</span> {{ $checkInStart }} – {{ $ontimeLimit }} WITA</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Pindai kartu siswa untuk mencatat absen masuk atau pulang.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1" id="mode-switch">
                    <button type="button" data-mode="in" class="rounded-lg px-3.5 py-2 text-[12px] font-bold transition {{ $initialMode === 'in' ? 'bg-brand-500 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                        <span class="material-symbols-outlined align-middle text-[16px]">login</span> Masuk Pagi
                    </button>
                    <button type="button" data-mode="out" class="rounded-lg px-3.5 py-2 text-[12px] font-bold transition {{ $initialMode === 'out' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-50' }}">
                        <span class="material-symbols-outlined align-middle text-[16px]">logout</span> Pulang Sore
                    </button>
                </div>
                <button type="button" id="sound-toggle" aria-pressed="true" title="Bunyi saat presensi berhasil"
                    class="btn-secondary">
                    <span class="material-symbols-outlined text-[19px]" id="sound-icon">volume_up</span>
                    <span id="sound-label" class="hidden sm:inline">Suara Aktif</span>
                </button>
                <button type="button" id="fullscreen-btn" class="btn-secondary">
                    <span class="material-symbols-outlined text-[19px]">fullscreen</span>
                    Layar Penuh
                </button>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-5">
            {{-- Viewfinder --}}
            <div class="space-y-5 xl:col-span-3">
                <div class="card overflow-hidden">
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#0b1220] sm:aspect-[16/10]">
                        <div id="qr-reader" class="absolute inset-0 h-full w-full [&_video]:h-full [&_video]:w-full [&_video]:object-cover"></div>

                        {{-- Overlay --}}
                        <div class="pointer-events-none absolute inset-0">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,rgba(2,6,23,0.55)_100%)]"></div>
                            <div class="absolute left-1/2 top-1/2 h-[62%] w-[62%] max-h-[340px] max-w-[340px] -translate-x-1/2 -translate-y-1/2 sm:h-[58%] sm:w-[52%]">
                                <span class="absolute left-0 top-0 h-10 w-10 rounded-tl-2xl border-l-[3px] border-t-[3px] border-emerald-400"></span>
                                <span class="absolute right-0 top-0 h-10 w-10 rounded-tr-2xl border-r-[3px] border-t-[3px] border-emerald-400"></span>
                                <span class="absolute bottom-0 left-0 h-10 w-10 rounded-bl-2xl border-b-[3px] border-l-[3px] border-emerald-400"></span>
                                <span class="absolute bottom-0 right-0 h-10 w-10 rounded-br-2xl border-b-[3px] border-r-[3px] border-emerald-400"></span>
                                <div class="scanline absolute left-3 right-3 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_18px_rgba(52,211,153,0.9)]"></div>
                            </div>
                        </div>

                        <div class="absolute left-4 top-4 flex items-center gap-2 rounded-lg bg-black/50 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-white/80 backdrop-blur">
                            <span class="pulse-dot h-1.5 w-1.5 rounded-full bg-rose-400"></span> Live • Kamera 01
                        </div>
                        <div class="absolute right-4 top-4 rounded-lg bg-black/50 px-2.5 py-1.5 text-[10px] font-semibold text-white/70 backdrop-blur">
                            <span class="material-symbols-outlined align-middle text-[13px]">photo_camera</span> CMOS 1080p
                        </div>

                        <div class="absolute bottom-4 left-1/2 w-[88%] -translate-x-1/2 rounded-xl bg-black/55 px-4 py-2.5 text-center backdrop-blur">
                            <p class="text-[12px] font-semibold text-white" id="scanner-hint">Arahkan kartu pelajar ke area bidik QR Code</p>
                            <p class="mt-0.5 text-[10px] text-white/50" id="scanner-subhint">Mode <span id="mode-label">Masuk Pagi</span> • validasi waktu otomatis oleh sistem</p>
                        </div>

                        <div class="absolute bottom-20 right-4 flex flex-col gap-2">
                            <button type="button" id="flip-camera" title="Ganti kamera" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white/80 backdrop-blur transition hover:bg-white/20">
                                <span class="material-symbols-outlined text-[20px]">cameraswitch</span>
                            </button>
                            <button type="button" id="toggle-camera" title="Hentikan kamera" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white/80 backdrop-blur transition hover:bg-white/20">
                                <span class="material-symbols-outlined text-[20px]">videocam_off</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2 text-[11px] text-slate-500">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><span class="material-symbols-outlined text-[16px]">bolt</span></span>
                            Pemindaian tercatat &lt; 1 detik • notifikasi WA asynchronous
                        </div>
                        <button type="button" data-modal-open="manualModal" class="btn-soft py-2 text-[12px]">
                            <span class="material-symbols-outlined text-[17px]">keyboard</span>
                            Input NISN Manual
                        </button>
                    </div>
                </div>

                {{-- Input manual inline --}}
                <div class="card p-4">
                    <form id="manual-form" class="flex flex-col gap-2 sm:flex-row">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[19px] text-slate-400">qr_code</span>
                            <input id="manual-input" type="text" inputmode="numeric" autocomplete="off" placeholder="Scan kartu atau ketik NISN / kode QR lalu Enter…"
                                class="input py-3 pl-11 font-medium tracking-wide">
                        </div>
                        <button type="submit" class="btn-primary shrink-0">
                            <span class="material-symbols-outlined text-[18px]">send</span> Proses Presensi
                        </button>
                    </form>
                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[10px] text-slate-400">
                        <span>Uji cepat:</span>
                        @foreach ($demoStudents as $demo)
                            <button type="button" data-demo-scan="{{ $demo->qrPayload() }}" class="rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-medium text-slate-500 transition hover:border-brand-300 hover:text-brand-600">
                                {{ $demo->name }} ({{ $demo->nisn }})
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Result panel --}}
            <div class="space-y-5 xl:col-span-2">
                <div class="card overflow-hidden" id="result-card">
                    {{-- Default --}}
                    <div id="scan-empty" class="flex flex-col items-center justify-center px-6 py-14 text-center">
                        <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-brand-50 text-brand-500 ring-8 ring-brand-50/50">
                            <span class="material-symbols-outlined text-[44px]">qr_code_scanner</span>
                            <span class="pulse-dot absolute -right-1 top-0 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                        </div>
                        <h3 class="mt-5 text-[16px] font-bold text-ink">Siap memindai kartu</h3>
                        <p class="mt-1.5 max-w-xs text-[12px] leading-relaxed text-slate-500">
                            Kartu pelajar berisi QR Code unik berbasis NISN. Hasil pemindaian akan muncul di sini beserta status kehadiran dan notifikasi WhatsApp.
                        </p>
                        <div class="mt-5 grid w-full gap-2 text-left">
                            <div class="flex items-start gap-2 rounded-xl bg-slate-50 p-2.5">
                                <span class="material-symbols-outlined text-[16px] text-brand-500">check_circle</span>
                                <p class="text-[11px] leading-relaxed text-slate-500">Status hadir/terlambat ditentukan otomatis dari jam operasional.</p>
                            </div>
                            <div class="flex items-start gap-2 rounded-xl bg-slate-50 p-2.5">
                                <span class="material-symbols-outlined text-[16px] text-brand-500">check_circle</span>
                                <p class="text-[11px] leading-relaxed text-slate-500">Notifikasi ke orang tua dikirim melalui queue, bukan memblokir scan.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Error --}}
                    <div id="scan-error" class="hidden flex-col items-center justify-center px-6 py-14 text-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-50 text-rose-500 ring-8 ring-rose-50/60">
                            <span class="material-symbols-outlined text-[38px]">error</span>
                        </div>
                        <h3 class="mt-4 text-[16px] font-bold text-ink">Presensi tidak dapat diproses</h3>
                        <p id="error-message" class="mt-1.5 max-w-xs text-[12px] leading-relaxed text-slate-500">-</p>
                        <button type="button" id="retry-btn" class="btn-secondary mt-5">
                            <span class="material-symbols-outlined text-[17px]">refresh</span> Coba Lagi
                        </button>
                    </div>

                    {{-- Success --}}
                    <div id="scan-result" class="hidden">
                        <div id="result-header" class="relative overflow-hidden bg-gradient-to-br from-brand-700 to-brand-500 px-5 py-4 text-white">
                            <div class="absolute inset-0 bg-grid opacity-30"></div>
                            <div class="relative flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15"><span class="material-symbols-outlined text-[19px]">check</span></span>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60" id="result-eyebrow">Presensi Masuk</p>
                                        <p class="text-[14px] font-bold" id="result-title">Berhasil dicatat</p>
                                    </div>
                                </div>
                                <span class="rounded-lg bg-black/15 px-2 py-1 text-[11px] font-bold tabular-nums" id="result-time">--:--</span>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center gap-3">
                                <div id="result-avatar" class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-lg font-extrabold text-brand-700 ring-1 ring-brand-100">MF</div>
                                <div class="min-w-0 flex-1">
                                    <p id="result-name" class="truncate text-[16px] font-bold text-ink">Nama Siswa</p>
                                    <p id="result-meta" class="text-[11px] text-slate-500">NISN • Kelas</p>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                        <span id="result-status" class="badge-green">Hadir</span>
                                        <span id="result-notif" class="badge-slate">WA menunggu</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Jam Masuk</p>
                                    <p id="result-in" class="mt-0.5 text-sm font-bold tabular-nums text-slate-700">--:--</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Jam Pulang</p>
                                    <p id="result-out" class="mt-0.5 text-sm font-bold tabular-nums text-slate-700">-</p>
                                </div>
                            </div>

                            <div class="mt-4 rounded-2xl bg-[#e5ddd5] p-3">
                                <div class="mb-2 flex items-center gap-1.5 text-[10px] font-semibold text-slate-500">
                                    <span class="material-symbols-outlined text-[14px] text-emerald-600">whatsapp</span>
                                    Pratinjau notifikasi ke orang tua
                                </div>
                                <div class="ml-auto max-w-[95%] rounded-xl rounded-tr-sm bg-[#dcf8c6] px-3 py-2 shadow-sm">
                                    <p id="result-wa" class="whitespace-pre-line text-[11px] leading-relaxed text-slate-700">-</p>
                                    <p class="mt-1 text-right text-[9px] text-slate-500" id="result-wa-time">--:-- ✓✓</p>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2">
                                <span class="text-[10px] text-slate-400">Nomor wali</span>
                                <span id="result-phone" class="text-[11px] font-semibold text-slate-600">-</span>
                            </div>

                            <button type="button" id="next-scan" class="btn-primary mt-4 w-full">
                                <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                                Scan Berikutnya
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-[13px] font-bold text-ink">
                            <span class="material-symbols-outlined text-[17px] text-brand-500">history</span>
                            Antrean Gerbang Terakhir
                        </h3>
                        <span class="text-[10px] text-slate-400" id="recent-count">{{ $recent->count() }} pemindaian</span>
                    </div>
                    <div class="mt-2.5 space-y-1.5" id="recent-list">
                        @forelse ($recent as $attendance)
                            <div class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-slate-50">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-[9px] font-bold text-slate-600">{{ $attendance->student?->initials() }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[11px] font-semibold text-slate-600">{{ $attendance->student?->name }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $attendance->student?->schoolClass?->name }} • {{ $attendance->time_in ? substr($attendance->time_in, 0, 5) : '-' }}</p>
                                </div>
                                <span class="badge {{ $attendance->statusClasses() }} scale-90">{{ $attendance->statusLabel() }}</span>
                            </div>
                        @empty
                            <p class="rounded-lg bg-slate-50 px-3 py-5 text-center text-[11px] text-slate-400">Belum ada pemindaian hari ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pemindaian</p>
                <p class="mt-1 text-xl font-extrabold text-ink" id="stat-total">{{ $stats['total'] }}</p>
                <p class="text-[10px] text-slate-400">dari {{ $totalStudents }} siswa aktif</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Hadir Tepat Waktu</p>
                <p class="mt-1 text-xl font-extrabold text-emerald-600" id="stat-hadir">{{ $stats['hadir'] }}</p>
                <p class="text-[10px] text-slate-400">sebelum {{ $ontimeLimit }} WITA</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terlambat</p>
                <p class="mt-1 text-xl font-extrabold text-amber-600" id="stat-terlambat">{{ $stats['terlambat'] }}</p>
                <p class="text-[10px] text-slate-400">perlu pembinaan</p>
            </div>
            <div class="card p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Presensi Pulang</p>
                <p class="mt-1 text-xl font-extrabold text-sky-600" id="stat-pulang">{{ $stats['pulang'] }}</p>
                <p class="text-[10px] text-slate-400">mulai {{ $checkOutTime }} WITA</p>
            </div>
        </div>
    </div>

    {{-- Modal manual --}}
    <div data-modal id="manualModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-[16px] font-bold text-ink">Input NISN Manual</h3>
                    <p class="mt-0.5 text-[12px] text-slate-500">Gunakan jika kartu pelajar rusak atau kamera tidak tersedia.</p>
                </div>
                <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form id="modal-manual-form" class="mt-4 space-y-3">
                <div>
                    <label class="label" for="modal-nisn">NISN / Kode QR</label>
                    <input id="modal-nisn" data-autofocus type="text" autocomplete="off" placeholder="Contoh: 001000001" class="input font-medium tracking-wide">
                </div>
                <button type="submit" class="btn-primary w-full">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span> Proses Presensi
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (() => {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            let mode = @json($initialMode);
            let lastScan = { text: null, at: 0 };
            let submitting = false;
            let html5Qr = null;
            let cameraRunning = false;
            let currentCameraId = null;

            const $ = (id) => document.getElementById(id);
            const empty = $('scan-empty');
            const result = $('scan-result');
            const error = $('scan-error');
            const resultHeader = $('result-header');
            const resultCard = $('result-card');

            const showState = (state) => {
                empty.classList.toggle('hidden', state !== 'empty');
                empty.classList.toggle('flex', state === 'empty');
                result.classList.toggle('hidden', state !== 'result');
                error.classList.toggle('hidden', state !== 'error');
                error.classList.toggle('flex', state === 'error');
            };

            const setMode = (next) => {
                mode = next;
                document.querySelectorAll('[data-mode]').forEach((button) => {
                    const active = button.dataset.mode === mode;
                    button.classList.toggle('bg-brand-500', active && mode === 'in');
                    button.classList.toggle('text-white', active);
                    button.classList.toggle('shadow-sm', active);
                    button.classList.toggle('bg-sky-600', active && mode === 'out');
                    button.classList.toggle('text-slate-500', !active);
                    button.classList.toggle('hover:bg-slate-50', !active);
                });
                $('mode-label').textContent = mode === 'in' ? 'Masuk Pagi' : 'Pulang Sore';
                $('scanner-hint').textContent = mode === 'in'
                    ? 'Arahkan kartu pelajar ke area bidik QR Code'
                    : 'Pindai kartu untuk mencatat presensi pulang';
            };

            document.querySelectorAll('[data-mode]').forEach((button) => {
                button.addEventListener('click', () => setMode(button.dataset.mode));
            });

            // ---- Suara presensi ----
            // AudioContext hanya boleh berbunyi setelah ada interaksi pengguna,
            // jadi konteks dibuka sekali lewat sentuhan/klik pertama di halaman.
            const AudioEngine = (() => {
                let ctx = null;
                let enabled = true;

                try {
                    enabled = localStorage.getItem('absensi.sound') !== 'off';
                } catch (e) {
                    // localStorage tidak tersedia
                }

                const context = () => {
                    if (ctx) return ctx;
                    const Ctx = window.AudioContext || window.webkitAudioContext;
                    if (!Ctx) return null;
                    ctx = new Ctx();
                    return ctx;
                };

                const unlock = () => {
                    const c = context();
                    if (c && c.state === 'suspended') {
                        c.resume().catch(() => {});
                    }
                };

                ['pointerdown', 'touchstart', 'keydown'].forEach((event) => {
                    window.addEventListener(event, unlock, { passive: true });
                });

                const tone = (c, frequency, startAt, duration, level, type = 'sine') => {
                    const oscillator = c.createOscillator();
                    const gain = c.createGain();
                    oscillator.type = type;
                    oscillator.frequency.setValueAtTime(frequency, startAt);
                    gain.gain.setValueAtTime(0.0001, startAt);
                    gain.gain.exponentialRampToValueAtTime(level, startAt + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);
                    oscillator.connect(gain).connect(c.destination);
                    oscillator.start(startAt);
                    oscillator.stop(startAt + duration + 0.03);
                };

                // Nada bel: frekuensi dasar + oktaf tipis agar terdengar seperti "ting".
                const chime = (notes, level) => {
                    const c = context();
                    if (!c) return;
                    if (c.state === 'suspended') {
                        c.resume().catch(() => {});
                    }
                    const base = c.currentTime + 0.03;
                    notes.forEach(([frequency, offset, duration]) => {
                        tone(c, frequency, base + offset, duration, level);
                        tone(c, frequency * 2, base + offset, duration * 0.55, level * 0.15, 'triangle');
                    });
                };

                const vibrate = (pattern) => {
                    try {
                        navigator.vibrate?.(pattern);
                    } catch (e) {
                        // abaikan
                    }
                };

                const play = (notes, level, pattern) => {
                    if (!enabled) return;
                    chime(notes, level);
                    vibrate(pattern);
                };

                return {
                    isEnabled: () => enabled,
                    setEnabled: (value) => {
                        enabled = value;
                        try {
                            localStorage.setItem('absensi.sound', value ? 'on' : 'off');
                        } catch (e) {
                            // abaikan
                        }
                    },
                    unlock,
                    // Hadir tepat waktu: arpeggio naik yang cerah.
                    success: () => play([[880, 0, 0.18], [1108.73, 0.11, 0.18], [1318.51, 0.22, 0.45]], 0.3, [30, 40, 30]),
                    // Terlambat: masih positif, nadanya lebih rendah.
                    late: () => play([[659.25, 0, 0.18], [880, 0.13, 0.42]], 0.28, [45, 60, 45]),
                    // Presensi pulang.
                    out: () => play([[987.77, 0, 0.16], [783.99, 0.11, 0.16], [1174.66, 0.22, 0.42]], 0.28, [30, 40, 30]),
                    // Gagal: dua nada rendah.
                    error: () => play([[233.08, 0, 0.22], [185, 0.24, 0.34]], 0.22, [90, 70, 90]),
                };
            })();

            const toast = (message, type = 'error') => {
                const el = document.createElement('div');
                el.className = `fixed right-4 top-4 z-[110] flex max-w-xs items-start gap-2 rounded-2xl border bg-white p-3.5 shadow-xl animate-rise ${type === 'error' ? 'border-rose-200' : 'border-emerald-200'}`;
                el.innerHTML = `<span class="material-symbols-outlined text-[19px] ${type === 'error' ? 'text-rose-500' : 'text-emerald-600'}">${type === 'error' ? 'error' : 'check_circle'}</span><p class="text-[11px] leading-relaxed text-slate-600">${message}</p>`;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 4200);
            };

            const submitScan = async (value) => {
                const code = (value || '').trim();
                if (!code || submitting) return;

                const now = Date.now();
                if (lastScan.text === code && now - lastScan.at < 4000) return;
                lastScan = { text: code, at: now };
                submitting = true;

                $('scanner-hint').textContent = 'Memvalidasi kartu…';
                $('scanner-hint').classList.add('animate-pulse');

                try {
                    const response = await fetch('{{ route('scan.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ qr_code: code, type: mode }),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        AudioEngine.error();
                        $('error-message').textContent = payload.message || 'Terjadi kesalahan saat memproses presensi.';
                        showState('error');
                        toast(payload.message || 'Presensi gagal.', 'error');
                        return;
                    }

                    renderResult(payload);
                    if (payload.type === 'out') {
                        AudioEngine.out();
                    } else if (payload.attendance?.status === 'terlambat') {
                        AudioEngine.late();
                    } else {
                        AudioEngine.success();
                    }
                    showState('result');
                    refreshRecent();
                    $('manual-input').value = '';
                    $('modal-nisn').value = '';
                } catch (err) {
                    AudioEngine.error();
                    $('error-message').textContent = 'Tidak dapat menghubungi server presensi. Periksa koneksi jaringan.';
                    showState('error');
                } finally {
                    submitting = false;
                    $('scanner-hint').classList.remove('animate-pulse');
                    $('scanner-hint').textContent = mode === 'in'
                        ? 'Arahkan kartu pelajar ke area bidik QR Code'
                        : 'Pindai kartu untuk mencatat presensi pulang';
                }
            };

            const renderResult = (payload) => {
                const student = payload.student;
                const attendance = payload.attendance;
                const late = attendance.status === 'terlambat';

                resultHeader.className = 'relative overflow-hidden px-5 py-4 text-white ' +
                    (payload.type === 'out' ? 'bg-gradient-to-br from-sky-700 to-sky-500' :
                        (late ? 'bg-gradient-to-br from-amber-600 to-amber-400' : 'bg-gradient-to-br from-brand-700 to-brand-500'));

                $('result-eyebrow').textContent = payload.type === 'out' ? 'Presensi Pulang' : 'Presensi Masuk';
                $('result-title').textContent = payload.type === 'out' ? 'Pulang tercatat' : (late ? 'Tercatat terlambat' : 'Berhasil dicatat');
                $('result-time').textContent = payload.time.slice(0, 5) + ' WITA';
                $('result-name').textContent = student.name;
                $('result-meta').textContent = 'NISN ' + student.nisn + ' • ' + student.class;
                $('result-avatar').textContent = student.initials;
                if (student.photo_url) {
                    $('result-avatar').innerHTML = `<img src="${student.photo_url}" alt="" class="h-full w-full rounded-2xl object-cover">`;
                }
                $('result-status').className = 'badge ' + (late ? 'badge-amber' : (payload.type === 'out' ? 'badge-sky' : 'badge-green'));
                $('result-status').textContent = attendance.status_label;
                $('result-notif').className = 'badge-slate';
                $('result-notif').textContent = 'WA dalam antrean';
                $('result-in').textContent = attendance.time_in || '-';
                $('result-out').textContent = attendance.time_out || '-';
                $('result-wa').textContent = payload.wa_message;
                $('result-wa-time').textContent = payload.time.slice(0, 5) + ' ✓✓';
                $('result-phone').textContent = student.parent_phone || 'Belum terdaftar';
            };

            // Manual forms
            const manualForm = $('manual-form');
            manualForm.addEventListener('submit', (event) => {
                event.preventDefault();
                submitScan($('manual-input').value);
            });

            $('modal-manual-form').addEventListener('submit', (event) => {
                event.preventDefault();
                const value = $('modal-nisn').value;
                document.querySelector('[data-modal-open="manualModal"]')?.click();
                setTimeout(() => {
                    document.getElementById('manualModal').classList.add('hidden');
                    document.getElementById('manualModal').classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                    submitScan(value);
                }, 60);
            });

            document.querySelectorAll('[data-demo-scan]').forEach((button) => {
                button.addEventListener('click', () => submitScan(button.dataset.demoScan));
            });

            $('next-scan').addEventListener('click', () => {
                showState('empty');
                $('manual-input').focus();
            });

            $('retry-btn').addEventListener('click', () => showState('empty'));

            const soundToggle = $('sound-toggle');
            const paintSound = () => {
                const on = AudioEngine.isEnabled();
                $('sound-icon').textContent = on ? 'volume_up' : 'volume_off';
                $('sound-label').textContent = on ? 'Suara Aktif' : 'Suara Mati';
                soundToggle.setAttribute('aria-pressed', on ? 'true' : 'false');
                soundToggle.classList.toggle('opacity-60', !on);
            };
            paintSound();

            soundToggle.addEventListener('click', () => {
                const next = !AudioEngine.isEnabled();
                AudioEngine.setEnabled(next);
                paintSound();

                if (next) {
                    AudioEngine.unlock();
                    AudioEngine.success();
                }
            });

            $('fullscreen-btn').addEventListener('click', () => {
                const kiosk = document.getElementById('kiosk');
                if (!document.fullscreenElement) {
                    kiosk.requestFullscreen?.();
                } else {
                    document.exitFullscreen?.();
                }
            });

            // ---- Camera ----
            const startCamera = async () => {
                if (!window.Html5Qrcode || cameraRunning) return;

                try {
                    html5Qr = new Html5Qrcode('qr-reader', { verbose: false });
                    await html5Qr.start(
                        currentCameraId ? { deviceId: { exact: currentCameraId } } : { facingMode: 'environment' },
                        {
                            fps: 10,
                            qrbox: (viewfinderWidth, viewfinderHeight) => {
                                const edge = Math.floor(Math.min(viewfinderWidth, viewfinderHeight) * 0.62);
                                return { width: edge, height: edge };
                            },
                            aspectRatio: 1.6,
                        },
                        (decodedText) => submitScan(decodedText),
                        () => {}
                    );
                    cameraRunning = true;
                } catch (err) {
                    console.warn('Kamera tidak tersedia:', err);
                    $('scanner-hint').textContent = 'Kamera tidak tersedia — gunakan input NISN manual';
                    $('scanner-subhint').textContent = 'Izin kamera ditolak atau perangkat tidak memiliki kamera.';
                }
            };

            const stopCamera = async () => {
                if (html5Qr && cameraRunning) {
                    try {
                        await html5Qr.stop();
                        await html5Qr.clear();
                    } catch (e) {
                        // abaikan
                    }
                    cameraRunning = false;
                }
            };

            $('toggle-camera').addEventListener('click', async () => {
                if (cameraRunning) {
                    await stopCamera();
                    $('scanner-hint').textContent = 'Kamera dihentikan — input manual tetap aktif';
                } else {
                    await startCamera();
                }
            });

            $('flip-camera').addEventListener('click', async () => {
                try {
                    const cameras = await Html5Qrcode.getCameras();
                    if (!cameras || cameras.length < 2) {
                        toast('Hanya satu kamera terdeteksi.', 'error');
                        return;
                    }
                    const index = cameras.findIndex((camera) => camera.id === currentCameraId);
                    currentCameraId = cameras[(index + 1) % cameras.length].id;
                    await stopCamera();
                    await startCamera();
                } catch (e) {
                    toast('Tidak dapat mengganti kamera.', 'error');
                }
            });

            // ---- Recent ----
            const statusClass = (status) => ({
                hadir: 'badge-green',
                terlambat: 'badge-amber',
                izin: 'badge-sky',
                sakit: 'badge-violet',
                alpa: 'badge-rose',
            }[status] || 'badge-slate');

            const refreshRecent = async () => {
                try {
                    const response = await fetch('{{ route('scan.recent') }}', { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    const list = $('recent-list');
                    if (data.items.length === 0) {
                        list.innerHTML = '<p class="rounded-lg bg-slate-50 px-3 py-5 text-center text-[11px] text-slate-400">Belum ada pemindaian hari ini.</p>';
                    } else {
                        list.innerHTML = data.items.map((item) => `
                            <div class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-slate-50">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-[9px] font-bold text-slate-600">${item.initials || 'S'}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[11px] font-semibold text-slate-600">${item.name}</p>
                                    <p class="text-[9px] text-slate-400">${item.class || '-'} • ${item.time || '-'}</p>
                                </div>
                                <span class="badge ${statusClass(item.status)} scale-90">${item.status_label}</span>
                            </div>`).join('');
                    }
                    $('recent-count').textContent = data.items.length + ' pemindaian';
                    $('stat-total').textContent = data.stats.total;
                    $('stat-hadir').textContent = data.stats.hadir;
                    $('stat-terlambat').textContent = data.stats.terlambat;
                    $('stat-pulang').textContent = data.stats.pulang;
                } catch (e) {
                    // abaikan
                }
            };

            setMode(mode);
            showState('empty');
            startCamera();
            setInterval(refreshRecent, 8000);
        })();
    </script>
@endpush
