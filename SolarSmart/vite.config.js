import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // هذا الجزء يمنع Vite من محاولة تحليل ملفات الـ Blade
    build: {
        rollupOptions: {
            // نحدد فقط الملفات التي نريد بناءها
            input: {
                app: 'resources/js/app.js',
                css: 'resources/css/app.css'
            }
        }
    }
});