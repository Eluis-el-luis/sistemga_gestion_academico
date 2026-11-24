import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.setTheme = function (isDark) {
    const root = document.documentElement;
    if (isDark) {
        root.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        root.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: Boolean(isDark) } }));
};

window.toggleTheme = function () {
    const nextDark = !document.documentElement.classList.contains('dark');
    window.setTheme(nextDark);
    return nextDark;
};

Alpine.start();