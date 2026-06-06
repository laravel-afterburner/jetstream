import './bootstrap';
import Sortable from 'sortablejs';

// Make Sortable available globally for Blade templates
window.Sortable = Sortable;

function applyColorScheme(scheme) {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = scheme === 'dark' || (scheme === 'system' && prefersDark);

    document.documentElement.dataset.colorScheme = scheme;
    document.documentElement.classList.toggle('dark', isDark);
}

document.addEventListener('DOMContentLoaded', () => {
    const scheme = document.documentElement.dataset.colorScheme || 'system';
    applyColorScheme(scheme);
});

window.addEventListener('color-scheme-updated', (event) => {
    applyColorScheme(event.detail?.scheme ?? 'system');
});

document.addEventListener('livewire:init', () => {
    Livewire.on('color-scheme-updated', ({ scheme }) => {
        applyColorScheme(scheme ?? 'system');
        window.dispatchEvent(new CustomEvent('color-scheme-updated', { detail: { scheme } }));
    });
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const scheme = document.documentElement.dataset.colorScheme || 'system';

    if (scheme === 'system') {
        applyColorScheme('system');
    }
});
