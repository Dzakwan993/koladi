import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                // Dashboard
                'resources/js/dashboard.js',
                'resources/css/dashboard.css',

                // chat
                'resources/js/chat.js',
                'resources/js/company-chat.js',

                // jadwal
                'resources/css/jadwal.css',
                'resources/js/jadwal.js',

                // statistik - TAMBAHKAN INI
                'resources/css/statistik.css',
                'resources/js/statistik.js',
            ],
            refresh: true,
        }),
    ],
});