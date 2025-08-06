const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.css', // LIGNE AJOUTÉE ICI
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                'primary': '#D32F2F',
                'dark-main': '#0A0A0A',
                'dark-card': '#171717',
                'dark-text': '#E5E5E5',
                'secondary-text': '#6B7280',
                'border-dark': '#262626',
            },
            fontFamily: {
                'sans': ['Roboto', ...defaultTheme.fontFamily.sans],
                'heading': ['Teko', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};