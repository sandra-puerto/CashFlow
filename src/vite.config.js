import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/bootstrap.css',
                'resources/js/bootstrap.js',

                'resources/js/sweetalert.js',

                'resources/js/form-utils.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
