import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

const origin = process.env.DDEV_PRIMARY_URL
    ? `${process.env.DDEV_PRIMARY_URL}:5173`
    : 'http://localhost:5173'

export default defineConfig({
    base: './',
    publicDir: false,
    plugins: [
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin,
        cors: {
            origin: process.env.DDEV_PRIMARY_URL || /^http:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/,
        },
        watch: {
            ignored: ['**/webroot/**', '**/vendor/**', '**/tmp/**', '**/logs/**'],
        },
    },
    build: {
        outDir: 'webroot/build',
        manifest: true,
        rollupOptions: {
            input: ['resources/js/app.js', 'resources/css/style.css'],
        },
    },
})
