import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';


export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                // jadwal
                'resources/css/jadwal.css',
                'resources/js/jadwal.js',
            ],  
            refresh: true,
        }),
    ],
});
