import './bootstrap';

import Alpine from 'alpinejs';

// Theme switching functionality
const theme = {
    init() {
        this.isDark = localStorage.getItem('theme') === 'dark' || 
                     (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        this.updateTheme();
    },
    
    toggle() {
        this.isDark = !this.isDark;
        this.updateTheme();
    },
    
    updateTheme() {
        if (this.isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }
};

// Register theme data with Alpine
Alpine.data('theme', () => theme);

window.Alpine = Alpine;

Alpine.start();
