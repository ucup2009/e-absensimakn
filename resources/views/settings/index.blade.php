@extends('layouts.app')

@section('title', 'Sistem')
@section('breadcrumb', 'Sistem')

@php
    $value = fn (string $key, $default = null) => $settings[$key] ?? $default;
    $isOn = fn (string $key, bool $default = false) => (bool) ($settings[$key] ?? $default);
@endphp

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="badge-green"><span class="material-symbols-outlined text-[13px]">settings</span> Pusat Kendali</span>
                <p class="mt-1 text-sm text-slate-500">Atur jam absen, pesan WhatsApp, dan cadangan data.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">schedule</span> Zona WITA (UTC+8)</span>
                <span class="badge-green"><span class="pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Queue {{ $queue['pending'] }} tertunda</span>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-5 xl:col-span-2">
                @csrf
                @method('PUT')

                {{-- Identitas --}}
                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">school</span> Identitas Madrasah &amp; Akademik
                    </h3>
                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="label" for="school_name">Nama Madrasah</label>
                            <input id="school_name" name="school_name" value="{{ $value('school_name', 'MAKN Ende') }}" required class="input">
                        </div>
                        <div>
                            <label class="label" for="academic_year">Tahun Ajaran</label>
                            <input id="academic_year" name="academic_year" value="{{ $value('academic_year', '2025/2026') }}" required class="input">
                        </div>
                        <div>
                            <label class="label" for="semester">Semester</label>
                            <select id="semester" name="semester" class="select">
                                <option value="Ganjil" @selected($value('semester') === 'Ganjil')>Ganjil</option>
                                <option value="Genap" @selected($value('semester') === 'Genap')>Genap</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="timezone">Zona Waktu</label>
                            <select id="timezone" name="timezone" class="select">
                                <option value="Asia/Makassar" @selected($value('timezone') === 'Asia/Makassar')>Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura" @selected($value('timezone') === 'Asia/Jayapura')>Asia/Jayapura (WIT)</option>
                                <option value="Asia/Jakarta" @selected($value('timezone') === 'Asia/Jakarta')>Asia/Jakarta (WIB)</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="attendance_target">Target Kehadiran (%)</label>
                            <input id="attendance_target" type="number" name="attendance_target" value="{{ $value('attendance_target', 90) }}" min="50" max="100" class="input">
                        </div>
                    </div>
                </div>

                {{-- Jam operasional --}}
                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">schedule</span> Jadwal &amp; Aturan Jam Presensi
                    </h3>
                    <div class="mt-4 grid gap-3.5 sm:grid-cols-3">
                        <div>
                            <label class="label" for="check_in_start">Jam Masuk Mulai</label>
                            <input id="check_in_start" type="time" name="check_in_start" value="{{ $value('check_in_start', '06:30') }}" required class="input">
                        </div>
                        <div>
                            <label class="label" for="check_in_limit">Batas Tepat Waktu</label>
                            <input id="check_in_limit" type="time" name="check_in_limit" value="{{ $value('check_in_limit', '07:15') }}" required class="input">
                        </div>
                        <div>
                            <label class="label" for="late_tolerance">Toleransi (menit)</label>
                            <input id="late_tolerance" type="number" name="late_tolerance" value="{{ $value('late_tolerance', 15) }}" min="0" max="120" class="input">
                        </div>
                        <div>
                            <label class="label" for="check_out_time">Jam Pulang Normal</label>
                            <input id="check_out_time" type="time" name="check_out_time" value="{{ $value('check_out_time', '15:30') }}" required class="input">
                        </div>
                        <div>
                            <label class="label" for="friday_check_out">Jam Pulang Jumat</label>
                            <input id="friday_check_out" type="time" name="friday_check_out" value="{{ $value('friday_check_out', '11:30') }}" required class="input">
                        </div>
                        <div class="flex items-end">
                            <span class="rounded-xl bg-slate-50 px-3 py-2.5 text-[10px] leading-relaxed text-slate-500">Scan setelah {{ $value('check_in_limit', '07:15') }} otomatis berstatus <strong>Terlambat</strong>.</span>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-slate-200 p-3">
                            <input type="checkbox" name="holiday_mode" value="1" @checked($isOn('holiday_mode')) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-500">
                            <span>
                                <span class="block text-[12px] font-semibold text-slate-700">Mode Libur / Tanggal Merah</span>
                                <span class="block text-[10px] text-slate-400">Presensi tidak dicatat saat mode ini aktif.</span>
                            </span>
                        </label>
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-slate-200 p-3">
                            <input type="checkbox" name="weather_tolerance" value="1" @checked($isOn('weather_tolerance')) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-500">
                            <span>
                                <span class="block text-[12px] font-semibold text-slate-700">Toleransi Cuaca Ekstrem</span>
                                <span class="block text-[10px] text-slate-400">Tambahan toleransi 20 menit untuk Ende.</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div class="card-pad scroll-mt-24" id="whatsapp">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">forum</span> Konfigurasi WhatsApp Gateway
                    </h3>
                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label class="label" for="wa_engine">Engine Gateway</label>
                            <select id="wa_engine" name="wa_engine" class="select">
                                <option value="simulator" @selected($value('wa_engine') === 'simulator')>Simulator (tanpa kirim)</option>
                                <option value="fonnte" @selected($value('wa_engine') === 'fonnte')>Fonnte Cloud API</option>
                                <option value="wablas" @selected($value('wa_engine') === 'wablas')>Wablas Cloud API</option>
                                <option value="baileys" @selected($value('wa_engine') === 'baileys')>Baileys Self-hosted</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="wa_api_key">API Key</label>
                            <input id="wa_api_key" name="wa_api_key" value="{{ $value('wa_api_key') }}" placeholder="Kosongkan untuk mode simulasi" class="input font-mono text-[12px]">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="wa_endpoint">Endpoint API</label>
                            <input id="wa_endpoint" name="wa_endpoint" value="{{ $value('wa_endpoint', 'https://api.fonnte.com/send') }}" class="input font-mono text-[12px]">
                        </div>
                        <div>
                            <label class="label" for="wa_delay">Delay Throttle (detik)</label>
                            <input id="wa_delay" type="number" step="0.1" name="wa_delay" value="{{ $value('wa_delay', 1.5) }}" min="0.5" max="30" class="input">
                        </div>
                        <div>
                            <label class="label" for="wa_quota">Kuota Pesan Bulanan</label>
                            <input id="wa_quota" type="number" name="wa_quota" value="{{ $value('wa_quota', 2000) }}" min="0" class="input">
                        </div>
                        <div>
                            <label class="label" for="wa_used">Pesan Terpakai</label>
                            <input id="wa_used" type="number" name="wa_used" value="{{ $value('wa_used', 0) }}" min="0" class="input">
                        </div>
                        <div class="flex items-end">
                            <label class="flex w-full cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2.5">
                                <input type="checkbox" name="wa_notifications_enabled" value="1" @checked($isOn('wa_notifications_enabled', true)) class="h-4 w-4 rounded border-slate-300 text-brand-500">
                                <span class="text-[12px] font-semibold text-slate-700">Notifikasi WhatsApp otomatis</span>
                            </label>
                        </div>
                    </div>
                    <p class="mt-3 rounded-xl bg-amber-50 p-3 text-[11px] leading-relaxed text-amber-700">
                        <span class="material-symbols-outlined align-middle text-[14px]">key</span>
                        API key disimpan di database. Untuk produksi, gunakan environment variable dan enkripsi konfigurasi.
                    </p>
                </div>

                {{-- EMIS --}}
                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">cloud_sync</span> Integrasi EMIS 4.0 Kemenag RI
                    </h3>
                    <div class="mt-4 grid gap-3.5 sm:grid-cols-2">
                        <div>
                            <label class="label" for="emis_school_code">Kode Madrasah</label>
                            <input id="emis_school_code" name="emis_school_code" value="{{ $value('emis_school_code') }}" class="input font-mono text-[12px]">
                        </div>
                        <div>
                            <label class="label" for="emis_endpoint">Endpoint EMIS</label>
                            <input id="emis_endpoint" name="emis_endpoint" value="{{ $value('emis_endpoint') }}" class="input font-mono text-[12px]">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="emis_token">OAuth2 Token</label>
                            <input id="emis_token" name="emis_token" value="{{ $value('emis_token') }}" placeholder="Token akses EMIS" class="input font-mono text-[12px]">
                        </div>
                        <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3 py-2.5 sm:col-span-2">
                            <input type="checkbox" name="emis_sync_enabled" value="1" @checked($isOn('emis_sync_enabled', true)) class="h-4 w-4 rounded border-slate-300 text-brand-500">
                            <span class="text-[12px] font-semibold text-slate-700">Sinkronisasi otomatis data kehadiran ke EMIS</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('settings.index') }}" class="btn-secondary">Reset</a>
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined text-[18px]">save</span> Simpan Semua Pengaturan
                    </button>
                </div>
            </form>

            {{-- Sidebar --}}
            <div class="space-y-5">
                <div class="card-pad">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                            <span class="material-symbols-outlined text-[19px] text-brand-500">layers</span> Queue Worker
                        </h3>
                        <span class="badge-green"><span class="pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Running</span>
                    </div>
                    <div class="mt-3 space-y-2 text-[12px]">
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Driver</span><span class="font-semibold text-slate-700">{{ ucfirst($queue['driver']) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Antrean tertunda</span><span class="font-semibold text-slate-700">{{ $queue['pending'] }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Job gagal</span><span class="font-semibold {{ $queue['failed'] > 0 ? 'text-rose-600' : 'text-slate-700' }}">{{ $queue['failed'] }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Active workers</span><span class="font-semibold text-slate-700">{{ $queue['workers'] }}</span>
                        </div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <form method="POST" action="{{ route('settings.queue.restart') }}" class="flex-1">
                            @csrf
                            <button type="submit" class="btn-secondary w-full py-2 text-[11px]">
                                <span class="material-symbols-outlined text-[15px]">restart_alt</span> Restart
                            </button>
                        </form>
                        <form method="POST" action="{{ route('settings.cache.flush') }}" class="flex-1">
                            @csrf
                            <button type="submit" class="btn-secondary w-full py-2 text-[11px]">
                                <span class="material-symbols-outlined text-[15px]">cleaning_services</span> Flush Cache
                            </button>
                        </form>
                    </div>
                    <p class="mt-2 text-[10px] leading-relaxed text-slate-400">Jalankan <code class="rounded bg-slate-100 px-1">php artisan queue:work</code> pada server produksi.</p>
                </div>

                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">backup</span> Backup Database
                    </h3>
                    <form method="POST" action="{{ route('settings.backup') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-primary w-full py-2 text-[12px]">
                            <span class="material-symbols-outlined text-[16px]">cloud_upload</span> Buat Snapshot Sekarang
                        </button>
                    </form>
                    <div class="mt-3 space-y-1.5">
                        @forelse ($backups as $backup)
                            <div class="flex items-center justify-between rounded-lg border border-slate-100 px-2.5 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-[10.5px] font-semibold text-slate-600">{{ $backup['name'] }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $backup['date']->format('d/m/Y H:i') }} • {{ $backup['size'] }}</p>
                                </div>
                                <a href="{{ route('settings.backup.download', $backup['name']) }}" class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-brand-600">
                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                </a>
                            </div>
                        @empty
                            <p class="rounded-lg bg-slate-50 px-3 py-4 text-center text-[10px] text-slate-400">Belum ada snapshot.</p>
                        @endforelse
                    </div>
                    <p class="mt-2 text-[10px] text-slate-400">Retensi 30 hari • tersimpan di storage/app/backups.</p>
                </div>

                <div class="card-pad">
                    <h3 class="flex items-center gap-2 text-[14px] font-bold text-ink">
                        <span class="material-symbols-outlined text-[19px] text-brand-500">manage_history</span> Audit Trail
                    </h3>
                    <div class="mt-3 space-y-2.5">
                        @forelse ($audits as $audit)
                            <div class="flex items-start gap-2.5">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><span class="material-symbols-outlined text-[13px]">history</span></span>
                                <div class="min-w-0">
                                    <p class="text-[10.5px] leading-snug text-slate-600">{{ $audit->description ?? $audit->action }}</p>
                                    <p class="text-[9px] text-slate-400">{{ $audit->user?->name ?? 'Sistem' }} • {{ $audit->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
