// E-Absensi MAKN Ende - interaksi global
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    const setSidebar = (open) => {
        if (!sidebar) return;
        sidebar.classList.toggle('-translate-x-full', !open);
        if (overlay) {
            overlay.classList.toggle('hidden', !open);
        }
        document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 1024);
    };

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isClosed = sidebar?.classList.contains('-translate-x-full');
            setSidebar(Boolean(isClosed));
        });
    });

    overlay?.addEventListener('click', () => setSidebar(false));

    // Panel filter di HP: buka/tutup tanpa reload.
    document.querySelectorAll('[data-filter-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const panel = button.closest('form')?.querySelector('[data-filter-panel]');
            if (!panel) return;

            const hidden = panel.classList.toggle('hidden');
            const label = button.querySelector('[data-filter-toggle-label]');
            if (label) label.textContent = hidden ? 'Buka' : 'Tutup';
            button.setAttribute('aria-expanded', String(!hidden));
        });
    });

    // Tutup menu samping setelah memilih halaman di HP
    sidebar?.querySelectorAll('a[href]').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) setSidebar(false);
        });
    });

    // PWA: daftarkan service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch((error) => {
                console.warn('Service worker gagal didaftarkan', error);
            });
        });
    }

    // PWA: tombol "Pasang aplikasi"
    let installPrompt = null;
    const installButtons = document.querySelectorAll('[data-install-app]');

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        installPrompt = event;
        installButtons.forEach((button) => {
            button.classList.remove('hidden');
            button.classList.add('flex');
        });
    });

    installButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            if (!installPrompt) return;
            installPrompt.prompt();
            await installPrompt.userChoice;
            installPrompt = null;
            button.classList.add('hidden');
            button.classList.remove('flex');
        });
    });

    window.addEventListener('appinstalled', () => {
        installButtons.forEach((button) => {
            button.classList.add('hidden');
            button.classList.remove('flex');
        });
    });

    // Jam WITA realtime
    const clocks = document.querySelectorAll('[data-clock]');
    const dateLabels = document.querySelectorAll('[data-date-label]');
    const tick = () => {
        const now = new Date();
        clocks.forEach((el) => {
            el.textContent = new Intl.DateTimeFormat('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: 'Asia/Makassar',
            }).format(now);
        });
        dateLabels.forEach((el) => {
            el.textContent = new Intl.DateTimeFormat('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                timeZone: 'Asia/Makassar',
            }).format(now);
        });
    };
    tick();
    setInterval(tick, 1000);

    // Modal helper
    document.querySelectorAll('[data-modal-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById(button.dataset.modalOpen);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            modal.querySelector('[data-autofocus]')?.focus();
        });
    });

    const closeModal = (modal) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = button.closest('[data-modal]');
            if (modal) closeModal(modal);
        });
    });

    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('[data-modal]:not(.hidden)').forEach(closeModal);
        }
    });

    // Konfirmasi aksi destruktif
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    // Pencarian siswa pada modal input presensi manual
    document.querySelectorAll('[data-student-picker]').forEach((picker) => {
        const input = picker.querySelector('input[data-student-search]') || picker.querySelector('input[type="search"], input[type="text"]');
        const hidden = picker.querySelector('[data-student-value]');
        const results = picker.parentElement?.querySelector('[data-student-results]');
        const hint = picker.parentElement?.querySelector('[data-student-hint]');
        const clear = picker.querySelector('[data-student-clear]');
        if (!input || !hidden || !results) return;

        let students = [];
        try {
            students = JSON.parse(picker.dataset.students || '[]');
        } catch (error) {
            console.warn('Data siswa tidak terbaca', error);
        }

        const defaultHint = hint ? hint.textContent : '';
        let activeIndex = -1;

        const normalize = (value) => String(value || '').toLowerCase().trim();

        const close = () => {
            results.classList.add('hidden');
            results.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        };

        const choose = (student) => {
            hidden.value = student.id;
            input.value = student.name;
            close();
            if (hint) {
                hint.textContent = `${student.name} — ${student.class || 'tanpa kelas'} (${student.nisn})`;
                hint.className = 'mt-1.5 text-[11px] font-semibold text-emerald-700';
            }
            clear?.classList.remove('hidden');
            clear?.classList.add('grid');
            input.setCustomValidity('');
        };

        const reset = () => {
            hidden.value = '';
            input.value = '';
            close();
            if (hint) {
                hint.textContent = defaultHint;
                hint.className = 'mt-1.5 text-[11px] text-slate-400';
            }
            clear?.classList.add('hidden');
            clear?.classList.remove('grid');
        };

        const render = (query) => {
            const q = normalize(query);
            const found = (q
                ? students.filter((student) => normalize(`${student.name} ${student.nisn} ${student.class}`).includes(q))
                : students
            ).slice(0, 30);

            results.innerHTML = '';

            if (!found.length) {
                results.innerHTML = '<p class="px-3 py-3 text-[12.5px] text-slate-400">Siswa tidak ditemukan.</p>';
                results.classList.remove('hidden');
                input.setAttribute('aria-expanded', 'true');
                activeIndex = -1;
                return;
            }

            found.forEach((student) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.setAttribute('role', 'option');
                item.className = 'flex min-h-[52px] w-full items-center gap-3 rounded-xl px-3 py-2 text-left transition hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none';
                item.innerHTML = `
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-[12px] font-bold text-emerald-700">
                        ${(student.name || '?').trim().charAt(0).toUpperCase()}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-[13px] font-semibold text-ink">${student.name}</span>
                        <span class="block truncate text-[11px] text-slate-500">${student.class || 'Tanpa kelas'} · ${student.nisn}</span>
                    </span>`;
                item.addEventListener('click', () => choose(student));
                results.appendChild(item);
            });

            results.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
            activeIndex = -1;
        };

        const move = (direction) => {
            const items = [...results.querySelectorAll('[role="option"]')];
            if (!items.length) return;
            activeIndex = (activeIndex + direction + items.length) % items.length;
            items.forEach((item, index) => item.classList.toggle('bg-emerald-50', index === activeIndex));
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        };

        input.addEventListener('input', () => {
            if (hidden.value) {
                hidden.value = '';
                clear?.classList.add('hidden');
                clear?.classList.remove('grid');
            }
            render(input.value);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim()) render(input.value);
        });
        input.addEventListener('click', () => render(input.value));
        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (results.classList.contains('hidden')) render(input.value);
                move(1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                move(-1);
            } else if (event.key === 'Enter') {
                const items = [...results.querySelectorAll('[role="option"]')];
                const target = items[activeIndex >= 0 ? activeIndex : 0];
                if (target) {
                    event.preventDefault();
                    target.click();
                }
            } else if (event.key === 'Escape') {
                if (!results.classList.contains('hidden')) {
                    event.stopPropagation();
                }
                close();
            }
        });

        clear?.addEventListener('click', () => {
            reset();
            input.focus();
        });

        document.addEventListener('click', (event) => {
            if (!picker.contains(event.target) && !results.contains(event.target)) close();
        });

        const form = picker.closest('form');
        form?.addEventListener('submit', (event) => {
            if (!hidden.value) {
                event.preventDefault();
                input.setCustomValidity('Pilih siswa dari daftar pencarian.');
                input.reportValidity();
                input.focus();
                render(input.value);
            }
        });

        input.addEventListener('input', () => input.setCustomValidity(''));
        if (hidden.value) choose(students.find((s) => String(s.id) === String(hidden.value)) || { id: hidden.value, name: input.value, nisn: '', class: '' });
    });

    // Flash auto hide
    document.querySelectorAll('[data-flash]').forEach((flash) => {
        setTimeout(() => {
            flash.style.transition = 'opacity .4s ease, transform .4s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-8px)';
            setTimeout(() => flash.remove(), 450);
        }, 5200);
    });

    // Copy
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                const original = button.innerHTML;
                button.innerHTML = '<span class="material-symbols-outlined text-[16px]">check</span> Tersalin';
                setTimeout(() => (button.innerHTML = original), 1600);
            } catch (error) {
                console.warn('Clipboard tidak tersedia', error);
            }
        });
    });
});
