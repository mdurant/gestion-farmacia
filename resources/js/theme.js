const STORAGE_KEY = 'acalis-theme';
const THEMES = ['acalis-light', 'acalis-dark'];

export function getStoredTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    return THEMES.includes(stored) ? stored : 'acalis-light';
}

export function applyTheme(theme) {
    const resolved = THEMES.includes(theme) ? theme : 'acalis-light';
    document.documentElement.setAttribute('data-theme', resolved);
    localStorage.setItem(STORAGE_KEY, resolved);
    return resolved;
}

export function initTheme() {
    applyTheme(getStoredTheme());
}

export function toggleTheme() {
    const current = getStoredTheme();
    const next = current === 'acalis-light' ? 'acalis-dark' : 'acalis-light';
    return applyTheme(next);
}

// Anti-flash: ejecutar antes del paint
initTheme();

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        current: getStoredTheme(),

        set(theme) {
            this.current = applyTheme(theme);
        },

        toggle() {
            this.current = toggleTheme();
        },

        isDark() {
            return this.current === 'acalis-dark';
        },
    });
});
