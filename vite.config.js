import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    root: '.',
    plugins: [
        tailwindcss(),
    ],
    build: {
        outDir: 'webroot/css',
        rollupOptions: {
            input: 'resources/css/style.css',
            output: {
                assetFileNames: 'cake.css',
            }
        }
    }
})
