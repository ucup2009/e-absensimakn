@extends('layouts.app')

@section('title', 'Pesan WhatsApp')
@section('breadcrumb', 'Pesan')

@section('content')
    <div class="mx-auto max-w-[1400px] space-y-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-green"><span class="pulse-dot h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Gateway aktif</span>
                    <span class="badge-slate"><span class="material-symbols-outlined text-[13px]">bolt</span> Engine: {{ ucfirst($engine) }}</span>
                    @if ($simulation)
                        <span class="badge-amber"><span class="material-symbols-outlined text-[13px]">science</span> Mode simulasi</span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-slate-500">Atur template pesan dan pantau pengiriman ke orang tua.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('settings.index') }}" class="btn-secondary">
                    <span class="material-symbols-outlined text-[18px]">settings</span> Konfigurasi Gateway
                </a>
                <a href="{{ route('scan.index') }}" class="btn-primary">
                    <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span> Buka Kiosk
                </a>
            </div>
        </div>

        @if ($simulation)
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-[12px] leading-relaxed text-amber-800">
                <span class="material-symbols-outlined text-[20px] text-amber-600">info</span>
                <div>
                    <p class="font-bold">Mode simulasi aktif</p>
                    <p class="mt-0.5 text-amber-700/80">API key gateway belum diisi. Pesan diproses dan dicatat sebagai berhasil tanpa benar-benar dikirim. Isi API key Fonnte/Wablas pada halaman Pengaturan untuk pengiriman nyata.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terkirim Bulan Ini</p>
                        <p class="mt-1 text-xl font-extrabold text-emerald-600">{{ number_format($stats['sent'], 0, ',', '.') }}</p>
                    </div>
                    <div class="stat-icon bg-emerald-50 text-emerald-600"><span class="material-symbols-outlined text-[20px]">done_all</span></div>
                </div>
                <p class="text-[10px] text-slate-400">{{ $stats['today'] }} pesan hari ini</p>
            </div>
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gagal</p>
                        <p class="mt-1 text-xl font-extrabold text-rose-600">{{ number_format($stats['failed'], 0, ',', '.') }}</p>
                    </div>
                    <div class="stat-icon bg-rose-50 text-rose-600"><span class="material-symbols-outlined text-[20px]">error</span></div>
                </div>
                <p class="text-[10px] text-slate-400">{{ $stats['pending'] }} menunggu antrean</p>
            </div>
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Latensi API</p>
                        <p class="mt-1 text-xl font-extrabold text-sky-600">42 <span class="text-xs font-semibold text-sky-500">ms</span></p>
                    </div>
                    <div class="stat-icon bg-sky-50 text-sky-600"><span class="material-symbols-outlined text-[20px]">speed</span></div>
                </div>
                <p class="text-[10px] text-slate-400">uptime 99.98%</p>
            </div>
            <div class="card p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kuota Pesan</p>
                        <p class="mt-1 text-xl font-extrabold text-brand-600">{{ number_format($stats['quota'] - $stats['used'], 0, ',', '.') }}</p>
                    </div>
                    <div class="stat-icon bg-brand-50 text-brand-600"><span class="material-symbols-outlined text-[20px]">sms</span></div>
                </div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-brand-500" style="width: {{ $stats['quota'] > 0 ? min(100, round($stats['used'] / $stats['quota'] * 100)) : 0 }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">{{ number_format($stats['used'], 0, ',', '.') }} / {{ number_format($stats['quota'], 0, ',', '.') }} terpakai</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-1.5">
            <button type="button" data-wa-tab="templates" class="tab-link tab-link-active">
                <span class="material-symbols-outlined text-[17px]">edit_note</span> Template Pesan
            </button>
            <button type="button" data-wa-tab="logs" class="tab-link">
                <span class="material-symbols-outlined text-[17px]">history</span> Log Pengiriman
                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-500">{{ $logs->total() }}</span>
            </button>
            <button type="button" data-wa-tab="test" class="tab-link">
                <span class="material-symbols-outlined text-[17px]">science</span> Uji Kirim
            </button>
        </div>

        {{-- Templates --}}
        <div data-wa-panel="templates" class="grid gap-5 xl:grid-cols-5">
            <div class="card p-5 xl:col-span-3">
                <div class="flex flex-wrap gap-1.5" data-template-tabs>
                    @foreach ($templates as $index => $template)
                        <button type="button" data-template-tab="{{ $index }}"
                            class="rounded-lg border px-2.5 py-1.5 text-[11px] font-semibold transition {{ $index === 0 ? 'border-brand-200 bg-brand-50 text-brand-700' : 'border-slate-200 text-slate-500 hover:bg-slate-50' }}">
                            {{ $template->name }}
                        </button>
                    @endforeach
                </div>

                @foreach ($templates as $index => $template)
                    <form method="POST" action="{{ route('whatsapp.templates.update', $template) }}" data-template-section="{{ $index }}" class="{{ $index === 0 ? '' : 'hidden' }} mt-4">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between">
                            <label class="label mb-0" for="body-{{ $template->id }}">Isi Pesan</label>
                            <span class="text-[10px] text-slate-400">{{ strlen($template->body) }} karakter</span>
                        </div>
                        <textarea id="body-{{ $template->id }}" name="body" rows="9" data-template-body
                            class="textarea mt-1.5 font-mono text-[12px] leading-relaxed">{{ $template->body }}</textarea>

                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach (['{nama_siswa}', '{nisn}', '{kelas}', '{nama_wali}', '{tanggal}', '{waktu_scan}', '{status}', '{jam_masuk}', '{jam_pulang}', '{nama_madrasah}'] as $placeholder)
                                <button type="button" data-placeholder="{{ $placeholder }}"
                                    class="rounded-md border border-slate-200 bg-slate-50 px-1.5 py-0.5 font-mono text-[10px] text-slate-500 transition hover:border-brand-300 hover:text-brand-600">{{ $placeholder }}</button>
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                            <label class="flex cursor-pointer items-center gap-2 text-[11px] font-medium text-slate-600">
                                <input type="checkbox" name="is_active" value="1" @checked($template->is_active) class="h-4 w-4 rounded border-slate-300 text-brand-500">
                                Aktifkan template ini
                            </label>
                            <button type="submit" class="btn-primary py-2 text-[12px]">
                                <span class="material-symbols-outlined text-[16px]">save</span> Simpan Template
                            </button>
                        </div>
                    </form>
                @endforeach
            </div>

            {{-- Chat preview --}}
            <div class="xl:col-span-2">
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-2.5 bg-[#075e54] px-4 py-3 text-white">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/20"><span class="material-symbols-outlined text-[18px]">school</span></div>
                        <div class="flex-1">
                            <p class="text-[12px] font-bold">MAKN Ende — Presensi</p>
                            <p class="text-[9px] text-white/60">online • bot notifikasi</p>
                        </div>
                        <span class="material-symbols-outlined text-[18px] text-white/70">more_vert</span>
                    </div>
                    <div class="max-h-[460px] min-h-[360px] overflow-y-auto bg-[#e5ddd5] p-4">
                        <div class="mx-auto mb-3 w-fit rounded-md bg-[#fff3c4] px-2 py-1 text-[9px] font-medium text-amber-800 shadow-sm">HARI INI</div>
                        <div class="ml-auto max-w-[92%] rounded-xl rounded-tr-sm bg-[#dcf8c6] px-3 py-2 shadow-sm">
                            <p data-wa-preview class="whitespace-pre-line text-[11.5px] leading-relaxed text-slate-700"></p>
                            <p class="mt-1 flex items-center justify-end gap-1 text-[9px] text-slate-500">
                                <span data-wa-preview-time>{{ now()->timezone('Asia/Makassar')->format('H:i') }}</span>
                                <span class="material-symbols-outlined text-[13px] text-sky-500">done_all</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 border-t border-slate-200 bg-[#f0f0f0] px-3 py-2">
                        <span class="material-symbols-outlined text-[20px] text-slate-400">mood</span>
                        <div class="flex-1 rounded-full bg-white px-3 py-1.5 text-[11px] text-slate-400">Pesan dikirim otomatis oleh sistem…</div>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#075e54] text-white"><span class="material-symbols-outlined text-[17px]">send</span></span>
                    </div>
                </div>
                <p class="mt-2 flex items-start gap-1.5 px-1 text-[10px] leading-relaxed text-slate-400">
                    <span class="material-symbols-outlined text-[13px]">info</span>
                    Preview mengikuti isi template. Tag dinamis diganti otomatis saat notifikasi dikirim.
                </p>
            </div>
        </div>

        {{-- Logs --}}
        <div data-wa-panel="logs" class="hidden space-y-4">
            <form method="GET" class="card p-4">
                <div class="grid gap-3 sm:grid-cols-5">
                    <div class="sm:col-span-2">
                        <label class="label" for="q">Cari</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Nomor atau nama siswa…" class="input">
                    </div>
                    <div>
                        <label class="label" for="log_status">Status</label>
                        <select id="log_status" name="status" class="select">
                            <option value="">Semua status</option>
                            <option value="sent" @selected($filters['status'] === 'sent')>Berhasil</option>
                            <option value="failed" @selected($filters['status'] === 'failed')>Gagal</option>
                            <option value="pending" @selected($filters['status'] === 'pending')>Menunggu</option>
                        </select>
                    </div>
                    <div>
                        <label class="label" for="type">Tipe</label>
                        <select id="type" name="type" class="select">
                            <option value="">Semua tipe</option>
                            <option value="check_in" @selected($filters['type'] === 'check_in')>Masuk</option>
                            <option value="check_out" @selected($filters['type'] === 'check_out')>Pulang</option>
                            <option value="warning" @selected($filters['type'] === 'warning')>Peringatan</option>
                            <option value="test" @selected($filters['type'] === 'test')>Uji Kirim</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary flex-1 py-2"><span class="material-symbols-outlined text-[17px]">filter_alt</span></button>
                        <a href="{{ route('whatsapp.index') }}" class="btn-secondary px-3 py-2"><span class="material-symbols-outlined text-[17px]">restart_alt</span></a>
                    </div>
                </div>
            </form>

            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left">
                        <thead class="table-head">
                            <tr>
                                <th class="px-5 py-2.5">Penerima</th>
                                <th class="px-3 py-2.5">Pesan</th>
                                <th class="px-3 py-2.5">Tipe</th>
                                <th class="px-3 py-2.5">Status</th>
                                <th class="px-3 py-2.5">Waktu</th>
                                <th class="px-5 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($logs as $log)
                                <tr class="transition hover:bg-slate-50/60">
                                    <td class="px-5 py-3">
                                        <p class="text-[12px] font-semibold tabular-nums text-slate-700">+{{ $log->phone }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $log->student?->name ?? 'Nomor uji' }}</p>
                                    </td>
                                    <td class="max-w-[280px] px-3 py-3">
                                        <p class="line-clamp-2 text-[11px] leading-relaxed text-slate-500">{{ \Illuminate\Support\Str::limit(str_replace(["\n", '*'], ' ', $log->message), 90) }}</p>
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="badge-slate">{{ ucfirst(str_replace('_', ' ', $log->type)) }}</span>
                                    </td>
                                    <td class="px-3 py-3"><span class="badge {{ $log->statusClasses() }}">{{ $log->statusLabel() }}</span></td>
                                    <td class="px-3 py-3">
                                        <p class="text-[11px] text-slate-600">{{ $log->created_at->timezone('Asia/Makassar')->format('d/m/Y') }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $log->created_at->timezone('Asia/Makassar')->format('H:i') }} WITA</p>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('whatsapp.resend', $log) }}">
                                            @csrf
                                            <button type="submit" class="btn-xs border border-slate-200 text-slate-600 hover:bg-slate-50">
                                                <span class="material-symbols-outlined text-[14px]">refresh</span> Kirim Ulang
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-slate-400">Belum ada log pengiriman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())
                    <div class="border-t border-slate-100 px-5 py-3">{{ $logs->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Test --}}
        <div data-wa-panel="test" class="hidden grid gap-5 xl:grid-cols-2">
            <div class="card-pad">
                <h3 class="flex items-center gap-2 text-[15px] font-bold text-ink">
                    <span class="material-symbols-outlined text-[19px] text-brand-500">send</span> Uji Kirim Notifikasi
                </h3>
                <p class="mt-1 text-[12px] text-slate-500">Verifikasi konektivitas gateway dan format pesan sebelum produksi.</p>
                <form method="POST" action="{{ route('whatsapp.test') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="label" for="test_phone">Nomor WhatsApp Tujuan</label>
                        <input id="test_phone" name="phone" required placeholder="6281234567890" inputmode="numeric" class="input tabular-nums">
                    </div>
                    <div>
                        <label class="label" for="test_message">Isi Pesan</label>
                        <textarea id="test_message" name="message" rows="5" required class="textarea">Assalamu'alaikum, ini adalah pesan uji dari Sistem E-Absensi MAKN Ende. Notifikasi WhatsApp berfungsi dengan baik.</textarea>
                    </div>
                    <button type="submit" class="btn-primary w-full">
                        <span class="material-symbols-outlined text-[18px]">send</span> Kirim Pesan Uji
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                <div class="card-pad">
                    <h3 class="text-[14px] font-bold text-ink">Status Konfigurasi</h3>
                    <div class="mt-3 space-y-2 text-[12px]">
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Engine</span><span class="font-semibold text-slate-700">{{ ucfirst($engine) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Notifikasi otomatis</span>
                            <span class="{{ $enabled ? 'badge-green' : 'badge-rose' }}">{{ $enabled ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Mode pengiriman</span>
                            <span class="{{ $simulation ? 'badge-amber' : 'badge-green' }}">{{ $simulation ? 'Simulasi' : 'Live API' }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                            <span class="text-slate-500">Endpoint</span><span class="max-w-[60%] truncate font-mono text-[10px] text-slate-600">{{ \App\Models\Setting::get('wa_endpoint', '-') }}</span>
                        </div>
                    </div>
                    <a href="{{ route('settings.index') }}#whatsapp" class="btn-secondary mt-3 w-full py-2 text-[12px]">Ubah Konfigurasi</a>
                </div>
                <div class="rounded-2xl bg-brand-50 p-4 text-[11px] leading-relaxed text-brand-800">
                    <p class="flex items-center gap-1.5 font-bold"><span class="material-symbols-outlined text-[15px]">science</span> Tips pengujian</p>
                    <p class="mt-1 text-brand-700/80">Gunakan nomor pribadi operator. Pesan uji tidak dikaitkan dengan siswa sehingga tidak memengaruhi statistik kehadiran.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Tabs halaman
        document.querySelectorAll('[data-wa-tab]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelectorAll('[data-wa-tab]').forEach((tab) => tab.classList.remove('tab-link-active'));
                button.classList.add('tab-link-active');
                document.querySelectorAll('[data-wa-panel]').forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.waPanel !== button.dataset.waTab);
                });
            });
        });

        // Tabs template
        const bodies = Array.from(document.querySelectorAll('[data-template-body]'));
        const renderPreview = (text) => {
            const escaped = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            const html = escaped.replace(/\*(.+?)\*/gs, '<strong>$1</strong>');
            const preview = document.querySelector('[data-wa-preview]');
            if (preview) preview.innerHTML = html || '<span class="text-slate-400">Tulis pesan template…</span>';
        };

        const activateTemplate = (index) => {
            document.querySelectorAll('[data-template-section]').forEach((section) => {
                section.classList.toggle('hidden', section.dataset.templateSection !== String(index));
            });
            document.querySelectorAll('[data-template-tab]').forEach((tab) => {
                const active = tab.dataset.templateTab === String(index);
                tab.classList.toggle('border-brand-200', active);
                tab.classList.toggle('bg-brand-50', active);
                tab.classList.toggle('text-brand-700', active);
                tab.classList.toggle('border-slate-200', !active);
                tab.classList.toggle('text-slate-500', !active);
            });
            const body = bodies[index];
            if (body) renderPreview(body.value);
        };

        document.querySelectorAll('[data-template-tab]').forEach((tab) => {
            tab.addEventListener('click', () => activateTemplate(tab.dataset.templateTab));
        });

        bodies.forEach((body) => {
            body.addEventListener('input', () => renderPreview(body.value));
        });

        document.querySelectorAll('[data-placeholder]').forEach((chip) => {
            chip.addEventListener('click', () => {
                const index = Array.from(document.querySelectorAll('[data-template-section]')).findIndex((section) => !section.classList.contains('hidden'));
                const body = bodies[index] || bodies[0];
                if (!body) return;
                const start = body.selectionStart ?? body.value.length;
                const end = body.selectionEnd ?? body.value.length;
                body.value = body.value.slice(0, start) + chip.dataset.placeholder + body.value.slice(end);
                body.focus();
                body.selectionStart = body.selectionEnd = start + chip.dataset.placeholder.length;
                renderPreview(body.value);
            });
        });

        if (bodies.length) activateTemplate(0);
    </script>
@endpush
