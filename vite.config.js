import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/chat.js',         // Untuk workspace chat
                'resources/js/company-chat.js', // Untuk company chat
            ],
            refresh: true,
        }),
    ],
});
