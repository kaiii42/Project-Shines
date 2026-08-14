(() => {
    'use strict';

    const header = document.querySelector('[data-site-header]');
    const navToggle = document.querySelector('[data-nav-toggle]');
    const siteNav = document.querySelector('[data-site-nav]');

    if (header) {
        const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 24);
        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });
    }

    if (navToggle && siteNav) {
        navToggle.addEventListener('click', () => {
            const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', String(!isOpen));
            siteNav.classList.toggle('is-open', !isOpen);
            document.body.classList.toggle('menu-open', !isOpen);
        });

        siteNav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navToggle.setAttribute('aria-expanded', 'false');
                siteNav.classList.remove('is-open');
                document.body.classList.remove('menu-open');
            });
        });
    }

    const videoToggle = document.querySelector('[data-video-toggle]');
    const heroVideo = document.querySelector('#hero-youtube');

    if (videoToggle && heroVideo) {
        let playing = true;
        videoToggle.addEventListener('click', () => {
            playing = !playing;
            heroVideo.contentWindow?.postMessage(JSON.stringify({
                event: 'command',
                func: playing ? 'playVideo' : 'pauseVideo',
                args: [],
            }), '*');
            videoToggle.setAttribute('aria-pressed', String(!playing));
            const icon = videoToggle.querySelector('[data-video-icon]');
            const label = videoToggle.querySelector('[data-video-label]');
            if (icon) icon.textContent = playing ? 'Ⅱ' : '▶';
            if (label) label.textContent = playing ? 'Jeda latar' : 'Putar latar';
        });
    }

    const lyrics = document.querySelector('[data-lyrics-content]');
    const increaseFont = document.querySelector('[data-font-increase]');
    const decreaseFont = document.querySelector('[data-font-decrease]');
    const copyButton = document.querySelector('[data-copy-lyrics]');
    let lyricsSize = 1;

    const updateLyricsSize = () => {
        if (!lyrics) return;
        lyrics.style.setProperty('--lyrics-scale', String(lyricsSize));
    };

    increaseFont?.addEventListener('click', () => {
        lyricsSize = Math.min(1.3, Math.round((lyricsSize + 0.1) * 10) / 10);
        updateLyricsSize();
    });

    decreaseFont?.addEventListener('click', () => {
        lyricsSize = Math.max(0.8, Math.round((lyricsSize - 0.1) * 10) / 10);
        updateLyricsSize();
    });

    copyButton?.addEventListener('click', async () => {
        if (!lyrics) return;
        const originalLabel = copyButton.dataset.copyLabel || 'Salin lirik';
        try {
            await navigator.clipboard.writeText(lyrics.innerText.trim());
            copyButton.textContent = 'Tersalin ✓';
        } catch (_) {
            copyButton.textContent = 'Gagal menyalin';
        }
        window.setTimeout(() => { copyButton.textContent = originalLabel; }, 1800);
    });

    const adminMenu = document.querySelector('[data-admin-menu]');
    const adminSidebar = document.querySelector('[data-admin-sidebar]');
    const adminMobile = window.matchMedia('(max-width: 900px)');

    const setAdminSidebar = (open, restoreFocus = false) => {
        if (!adminMenu || !adminSidebar) return;
        const shouldOpen = adminMobile.matches && open;
        adminMenu.setAttribute('aria-expanded', String(shouldOpen));
        adminSidebar.classList.toggle('is-open', shouldOpen);

        if (adminMobile.matches && !shouldOpen) {
            adminSidebar.setAttribute('inert', '');
            adminSidebar.setAttribute('aria-hidden', 'true');
        } else {
            adminSidebar.removeAttribute('inert');
            adminSidebar.removeAttribute('aria-hidden');
        }

        if (shouldOpen) {
            adminSidebar.querySelector('a, button')?.focus();
        } else if (restoreFocus) {
            adminMenu.focus();
        }
    };

    if (adminMenu && adminSidebar) {
        setAdminSidebar(false);
        adminMenu.addEventListener('click', () => {
            const isOpen = adminMenu.getAttribute('aria-expanded') === 'true';
            setAdminSidebar(!isOpen, isOpen);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && adminMenu.getAttribute('aria-expanded') === 'true') {
                setAdminSidebar(false, true);
            }
        });
        adminMobile.addEventListener('change', () => setAdminSidebar(false));
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-alert]').forEach((alert) => {
        window.setTimeout(() => alert.classList.add('is-fading'), 5000);
    });
})();
