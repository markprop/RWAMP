import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        // Enable manifest for cache busting
        manifest: true,
        // Generate source maps in production for debugging (optional)
        sourcemap: false,
        // Optimize chunk splitting
        rollupOptions: {
            output: {
                // Ensure consistent chunk naming for cache busting
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
        },
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
    },
    // Ensure proper cache busting in production
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
