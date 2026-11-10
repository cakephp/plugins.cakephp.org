import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    root: '.',
    plugins: [
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/webroot/**', '**/vendor/**', '**/tmp/**', '**/logs/**'],
        },
    },
    build: {
        outDir: 'webroot',
        emptyOutDir: false,
        lib: {
            entry: 'resources/js/app.js',
            name: 'CakePlugins',
            formats: ['iife'],
            fileName: () => 'js/app.js',
            cssFileName: 'cake',
        },
        rollupOptions: {
            output: {
                assetFileNames: asset => asset.names.includes('cake.css') ? 'css/cake.css' : 'assets/[name][extname]',
            },
        },
    },
})
