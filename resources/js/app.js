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
