import { defineConfig, devices } from '@playwright/test'
import { fileURLToPath } from 'node:url'

const database = fileURLToPath(new URL('./tmp/browser.sqlite', import.meta.url))
export default defineConfig({
    testDir: './tests/browser',
    workers: 1,
    use: { baseURL: 'http://127.0.0.1:8765', trace: 'retain-on-failure' },
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
    webServer: {
        command: 'php tests/browser/setup.php && php -d session.save_path=tmp/browser-sessions -S 127.0.0.1:8765 -t webroot webroot/index.php 2>tmp/browser-server.log',
        url: 'http://127.0.0.1:8765',
        reuseExistingServer: false,
        env: {
            APP_NAME: 'plugins-browser-tests',
            DATABASE_URL: `sqlite://127.0.0.1/${database}`,
            DEBUG: 'false',
            RATE_LIMIT_ENABLED: 'false',
            RATE_LIMIT_CACHE_URL: 'array://',
            CACHE_DEFAULT_URL: 'array://',
            CACHE_CATALOGUE_URL: `file://${fileURLToPath(new URL('./tmp/browser-catalogue/', import.meta.url))}`,
            CACHE_GITHUB_PROFILES_URL: `file://${fileURLToPath(new URL('./tmp/browser-github-profiles/', import.meta.url))}`,
            CACHE_MODEL_URL: 'array://',
            CACHE_PERSISTENT_URL: 'array://',
        },
    },
})
