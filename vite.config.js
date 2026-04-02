import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    root: '.',
    plugins: [
        tailwindcss(),
    ],
    build: {
        outDir: 'webroot',
        emptyOutDir: false,
        rollupOptions: {
            input: {
                cake: 'resources/css/style.css',
                app: 'resources/js/app.js',
            },
            output: {
                entryFileNames: (chunkInfo) => chunkInfo.name === 'app' ? 'js/app.js' : 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.names.includes('cake.css')) {
                        return 'css/cake.css';
                    }

                    return 'assets/[name][extname]';
                },
            },
        },
    },
})
