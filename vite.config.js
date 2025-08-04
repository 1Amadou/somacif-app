import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Ajout de cette section pour configurer le serveur de développement Vite
    server: {
        host: '0.0.0.0', // Permet d'écouter sur toutes les interfaces réseau
        hmr: {
            host: 'localhost',
        },
    },
});