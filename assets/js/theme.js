// ============================================================
// theme.js — Single Source of Truth for Theme Management
// Storage key: "theme" ("light" | "dark")
// Automatically updates data-theme attribute, Lucide icons, and meta theme-color.
// ============================================================
(function () {
    const KEY = 'theme';
    const root = document.documentElement;
    const mq = window.matchMedia('(prefers-color-scheme: dark)');

    const MOON_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>';
    const SUN_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>';

    const BTN_SELECTOR = '#themeToggle, #themeToggleMobile, #themeToggleSidebar, .theme-toggle-btn, [data-action="toggle-theme"]';

    function getSaved() {
        try {
            const urlTheme = new URLSearchParams(window.location.search).get('theme');
            if (urlTheme === 'dark' || urlTheme === 'light') return urlTheme;
            const v = localStorage.getItem(KEY);
            return v === 'dark' || v === 'light' ? v : null;
        } catch (e) {
            return null;
        }
    }

    function save(theme) {
        try { localStorage.setItem(KEY, theme); } catch (e) {}
    }

    function paint(theme, animate) {
        if (animate) {
            root.classList.add('theme-transition');
            window.setTimeout(() => root.classList.remove('theme-transition'), 300);
        }
        root.setAttribute('data-theme', theme);

        const isDark = theme === 'dark';
        const buttons = document.querySelectorAll(BTN_SELECTOR);
        buttons.forEach(btn => {
            btn.innerHTML = isDark ? SUN_ICON : MOON_ICON;
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            btn.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            btn.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        });

        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', isDark ? '#121814' : '#F4F6F4');
    }

    function current() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function toggle() {
        const next = current() === 'dark' ? 'light' : 'dark';
        save(next);
        paint(next, true);
    }

    function init() {
        const initial = getSaved() || (mq.matches ? 'dark' : 'light');
        paint(initial, false);

        document.addEventListener('click', function (e) {
            const btn = e.target.closest(BTN_SELECTOR);
            if (btn) {
                e.preventDefault();
                toggle();
            }
        });
    }

    // Expose globally
    window.toggleTheme = toggle;
    window.getCurrentTheme = current;

    if (mq.addEventListener) {
        mq.addEventListener('change', e => {
            if (!getSaved()) paint(e.matches ? 'dark' : 'light', true);
        });
    }

    window.addEventListener('storage', e => {
        if (e.key === KEY && (e.newValue === 'dark' || e.newValue === 'light')) {
            paint(e.newValue, true);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
