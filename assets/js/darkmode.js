// ============================================================
// Dark mode toggle
// Include this after the DOM (before </body>), and put a button
// like this somewhere in your header/sidebar:
//   <button id="theme-toggle-btn" class="theme-toggle">🌙 Dark mode</button>
// ============================================================

(function () {
    const STORAGE_KEY = 'site-theme';
    const root = document.documentElement;
    const btn = document.getElementById('theme-toggle-btn');

    function applyTheme(theme) {
        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
            if (btn) btn.textContent = '☀️ Light mode';
        } else {
            root.removeAttribute('data-theme');
            if (btn) btn.textContent = '🌙 Dark mode';
        }
    }

    // On load: use saved preference, otherwise respect OS setting
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
        applyTheme(saved);
    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        applyTheme('dark');
    }

    if (btn) {
        btn.addEventListener('click', function () {
            const isDark = root.getAttribute('data-theme') === 'dark';
            const next = isDark ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem(STORAGE_KEY, next);
        });
    }
})();