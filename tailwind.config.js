// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  // Large coverage of your Blade, JS, Vue, and vendor files
  content: [
    './resources/**/*.blade.php',    // All Blade views, all levels
    './resources/**/*.js',
    './resources/**/*.vue',          // If you use Vue
    './resources/**/*.ts',
    './resources/**/*.tsx',
    './resources/**/*.jsx',
    './resources/**/*.md',
    './public/**/*.html',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './vendor/filament/**/*.blade.php',
  ],

  darkMode: 'class', // Active le mode sombre par classe (best practice)[11],

  theme: {
    // Ton design system regroupé
    colors: {
      transparent: 'transparent',
      current: 'currentColor',

      // Palette principale
      primary: { // Défini sous forme d'objet – adapte si besoin
        light: '#FF6659',
        DEFAULT: '#D32F2F', // Rouge SOMACIF
        dark: '#B71C1C',
      },
      secondary: {
        light: '#F48FB1',
        DEFAULT: '#C2185B',
        dark: '#880E4F',
      },

      // Thème sombre
      'dark-main': '#0A0A0A',
      'dark-card': '#171717',
      'dark-text': '#E5E5E5',

      // Thème clair
      'light-main': '#FFFFFF',
      'light-card': '#F9FAFB',
      'light-text': '#111827',

      // Utilitaires
      'secondary-text': '#6B7280',
      'border-light': '#E5E7EB',
      'border-dark': '#262626',

      // Etats
      success: '#4CAF50',
      warning: '#FFC107',
      error: '#F44336',

      // Gris utilitaire (utilisation de la palette native Tailwind)
      ...defaultTheme.colors,
    },

    // Typo
    fontFamily: {
      sans: ['Roboto', ...defaultTheme.fontFamily.sans],
      heading: ['Teko', 'sans-serif'],
      serif: defaultTheme.fontFamily.serif,
      mono: defaultTheme.fontFamily.mono,
    },

    // Breakpoints personnalisés
    screens: {
      xs: '475px',
      ...defaultTheme.screens,
    },

    // Extensions personnalisées
    extend: {
      spacing: {
        '128': '32rem',
        '144': '36rem',
        '1/7': '14.2857143%',
        '2/7': '28.5714286%',
        '3/7': '42.8571429%',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      boxShadow: {
        '3xl': '0 35px 60px -15px rgba(0,0,0,0.3)',
      },
      zIndex: {
        '60': '60',
        '70': '70',
      },
      opacity: {
        '15': '0.15',
      },
      // Ajoute ici tes propres composants utilitaires si besoin
    },
  },

  plugins: [
    forms,
    // Ajoute ici d’autres plugins Tailwind si besoin, par ex. line-clamp, typography :
    // require('@tailwindcss/typography'),
    // require('@tailwindcss/line-clamp'),
  ],
};
