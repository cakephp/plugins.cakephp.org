import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import { fileURLToPath } from 'node:url'

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
    resolve: {
        conditions: ['module', 'browser', 'development', 'import'],
        alias: {
            'slim-select/styles': fileURLToPath(new URL('./node_modules/slim-select/dist/slimselect.css', import.meta.url))
        }
    },
    build: {
        outDir: 'webroot',
        emptyOutDir: false,
        rollupOptions: {
            input: {
                cake: 'resources/css/style.css',
                app: 'resources/js/app.js',
            },
            onLog(level, log, handler) {
                // Suppress EVAL warning from htmx (known limitation, safe in this context)
                if (log.code === 'EVAL' && log.id?.includes('htmx')) {
                    return
                }
                handler(level, log)
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
