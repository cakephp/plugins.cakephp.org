import { test, expect } from '@playwright/test'

const rows = page => page.locator('[data-package-row]')
const hero = page => page.locator('[data-namespace-hero]')
const stats = page => page.getByRole('region', { name: 'Namespace statistics', exact: true })
const pageErrors = new WeakMap()
const chooseSort = async (page, label) => {
    await page.getByLabel('Sort plugins', { exact: true }).click()
    await page.locator('.sort-options').getByRole('link', { name: label, exact: true }).click()
}

test.beforeEach(async ({ page }) => {
    const errors = []
    page.on('pageerror', error => errors.push(error.message))
    pageErrors.set(page, errors)
    await page.route('https://avatars.githubusercontent.com/**', route => route.fulfill({
        contentType: 'image/png',
        path: 'webroot/img/packagist-logo.png',
    }))
    await page.route('https://github.com/*.png*', route => route.fulfill({
        contentType: 'image/png',
        path: 'webroot/img/packagist-logo.png',
    }))
})

test.afterEach(async ({ page }) => {
    expect(pageErrors.get(page)).toEqual([])
})

test('namespace hero totals and catalogue results use the Composer prefix', async ({ page }) => {
    await page.goto('/plugins/vendor')
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('vendor')
    await expect(hero(page).locator('[data-namespace-stat]')).toHaveCount(0)
    await expect(page.locator('[data-namespace-banner]').getByRole('link', { name: 'GitHub profile' })).toHaveAttribute('href', 'https://github.com/vendor')
    await expect(page.locator('[data-namespace-banner]').getByRole('link', { name: 'Packagist', exact: true })).toHaveAttribute('href', 'https://packagist.org/packages/vendor/')
    await expect(page.locator('[data-namespace-banner]').getByRole('link', { name: 'Packagist', exact: true })).toHaveAttribute('target', '_blank')
    await expect(page.locator('[data-namespace-banner]').getByRole('link', { name: 'GitHub profile' })).toHaveText('')
    await expect(page.locator('[data-namespace-banner]').getByRole('link', { name: 'Packagist', exact: true })).toHaveText('')
    const bannerBounds = await page.locator('[data-namespace-banner]').boundingBox()
    const navbarBounds = await page.locator('.site-header').boundingBox()
    expect(bannerBounds.x).toBe(0)
    expect(bannerBounds.width).toBe(page.viewportSize().width)
    expect(bannerBounds.y).toBe(navbarBounds.y + navbarBounds.height)
    const headingBounds = await hero(page).boundingBox()
    const statsBounds = await stats(page).boundingBox()
    expect(statsBounds.x).toBeGreaterThan(headingBounds.x + headingBounds.width)
    expect(Math.abs(statsBounds.y + statsBounds.height / 2 - headingBounds.y - headingBounds.height / 2)).toBeLessThan(1)
    expect(Math.abs(statsBounds.y + statsBounds.height / 2 - bannerBounds.y - bannerBounds.height / 2)).toBeLessThan(1)
    await expect(stats(page).locator('[data-namespace-stat="plugins"]')).toHaveText('23')
    await expect(stats(page).locator('[data-namespace-stat="downloads"]')).toHaveText('22.7k')
    await expect(stats(page).locator('[data-namespace-stat="downloads"]')).toHaveAttribute('aria-label', '22,701')
    await expect(stats(page).locator('[data-namespace-stat="stars"]')).toHaveText('2k')
    await expect(stats(page).locator('[data-namespace-stat="stars"]')).toHaveAttribute('aria-label', '2,001')
    await expect(rows(page)).toHaveCount(21)
    expect(await rows(page).evaluateAll(elements => elements.every(element => element.textContent.includes('vendor/package-')))).toBe(true)
    await expect(page.locator('[data-featured-plugins]')).toHaveCount(0)
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'http://127.0.0.1:8765/plugins/vendor')
    await expect(page.locator('meta[name="robots"]')).toHaveCount(0)
    await page.screenshot({ path: 'tmp/namespace-desktop.png', animations: 'disabled' })
})

test('monthly downloads sort works in the catalogue and namespace lists', async ({ page }) => {
    for (const path of ['/', '/plugins/vendor']) {
        await page.goto(path)
        await page.evaluate(() => { window.monthlySortMarker = 'retained' })
        await chooseSort(page, 'Monthly downloads')
        await expect(page).toHaveURL(/sort=monthly_downloads/)
        await expect(rows(page).first()).toContainText(path === '/' ? 'dereuromark/cakephp-tools' : 'vendor/package-01')
        await chooseSort(page, 'Monthly downloads ↓')
        await expect(page).toHaveURL(/direction=asc/)
        await expect(rows(page).first()).toContainText('vendor/package-23')
        expect(await page.evaluate(() => window.monthlySortMarker)).toBe('retained')
        await page.reload()
        await expect(page.getByLabel('Sort plugins', { exact: true })).toContainText('Monthly downloads')
        await expect(rows(page).first()).toContainText('vendor/package-23')
    }
})

test('search, version filters, sorting and reset retain the namespace and hero', async ({ page }) => {
    await page.goto('/plugins/vendor')
    await page.evaluate(() => { window.namespaceMarker = 'retained' })
    await chooseSort(page, 'Downloads')
    await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('package-01')
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('vendor/package-01')
    await page.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(rows(page)).toHaveCount(0)
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
    await expect(stats(page).locator('[data-namespace-stat="plugins"]')).toHaveText('23')
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'noindex,nofollow')
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(rows(page)).toHaveCount(21)
    await expect(page).toHaveURL('/plugins/vendor?sort=downloads')
    expect(await page.evaluate(() => window.namespaceMarker)).toBe('retained')
    await expect(page.getByRole('search')).toHaveAttribute('action', '/plugins/vendor')
})

test('pagination and history stay on the namespace page', async ({ page }) => {
    await page.goto('/plugins/vendor')
    await page.evaluate(() => { window.namespaceMarker = 'retained' })
    await page.getByRole('link', { name: 'Last', exact: true }).click()
    await expect(page).toHaveURL('/plugins/vendor?page=2')
    await expect(rows(page)).toHaveCount(2)
    await page.getByRole('link', { name: 'First', exact: true }).click()
    await expect(page).toHaveURL('/plugins/vendor')
    await expect(rows(page)).toHaveCount(21)
    await page.goBack()
    await expect(page).toHaveURL('/plugins/vendor?page=2')
    await expect(rows(page)).toHaveCount(2)
    await page.goForward()
    await expect(rows(page)).toHaveCount(21)
    expect(await page.evaluate(() => window.namespaceMarker)).toBe('retained')
    await page.reload()
    await expect(rows(page)).toHaveCount(21)
})

test('category choices and detail filters stay within the namespace', async ({ page }) => {
    await page.goto('/plugins/markstory')
    await page.getByText('Browse categories', { exact: true }).click()
    const categories = page.getByRole('group', { name: 'Plugin categories', exact: true })
    await expect(categories.getByRole('link', { name: 'Asset Management, 1 plugin', exact: true })).toBeVisible()
    await expect(categories.getByRole('link', { name: /Authentication/ })).toHaveCount(0)
    await categories.getByRole('link', { name: 'Asset Management, 1 plugin', exact: true }).click()
    await expect(page).toHaveURL('/plugins/markstory?category=asset-management')
    await expect(rows(page)).toHaveCount(1)
    await page.getByRole('button', { name: 'Awesome only', exact: true }).click()
    await expect(rows(page)).toHaveCount(1)
    await rows(page).getByRole('link', { name: 'markstory/asset_compress', exact: true }).click()
    const dialog = page.getByRole('dialog')
    await dialog.getByRole('link', { name: 'Code Analysis', exact: true }).click()
    await expect(dialog).not.toBeVisible()
    await expect(page).toHaveURL(/\/plugins\/markstory\?/)
    await expect(page).toHaveURL(/category=code-analysis/)
    await expect(rows(page)).toHaveCount(1)
    await expect(stats(page).locator('[data-namespace-stat="downloads"]')).toHaveText('5k')
    await rows(page).getByRole('link', { name: 'markstory/asset_compress', exact: true }).click()
    await dialog.getByRole('link', { name: 'PHP 8.5', exact: true }).click()
    await expect(rows(page)).toHaveCount(1)
    await expect(page).toHaveURL(/\/plugins\/markstory\?/)
    await expect(page).toHaveURL(/php_slugs/)
    await expect(stats(page).locator('[data-namespace-stat="plugins"]')).toHaveText('1')
})

test('namespace links navigate from rows, modal details and standalone details', async ({ page }) => {
    await page.goto('/')
    await expect(page.locator('#package-26').getByRole('link', { name: 'dereuromark/cakephp-tools', exact: true }))
        .toHaveAttribute('href', '/plugins/dereuromark/cakephp-tools')
    await expect(page.locator('[data-featured-plugins]').getByRole('link', { name: 'View markstory/asset_compress details', exact: true }))
        .toHaveAttribute('href', '/plugins/markstory/asset_compress')
    await page.evaluate(() => { window.namespaceMarker = 'retained' })
    await page.getByRole('link', { name: 'More by dereuromark', exact: true }).click()
    await expect(page).toHaveURL('/plugins/dereuromark')
    expect(await page.evaluate(() => window.namespaceMarker)).toBe('retained')
    await page.goBack()
    await expect(page).toHaveURL('/')
    await page.locator('#package-26').getByRole('link', { name: 'dereuromark/cakephp-tools', exact: true }).click()
    await page.getByRole('dialog').getByRole('link', { name: 'View all plugins by dereuromark', exact: true }).click()
    await expect(page).toHaveURL('/plugins/dereuromark')
    await expect(page.getByRole('dialog')).not.toBeVisible()
    await expect(hero(page)).toBeVisible()
    expect(await page.evaluate(() => window.namespaceMarker)).toBe('retained')
    await page.goto('/plugins/dereuromark/cakephp-tools')
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'http://127.0.0.1:8765/plugins/dereuromark/cakephp-tools')
    await page.evaluate(() => { window.namespaceMarker = 'retained' })
    await page.getByRole('link', { name: 'View all plugins by dereuromark', exact: true }).click()
    await expect(page).toHaveURL('/plugins/dereuromark')
    expect(await page.evaluate(() => window.namespaceMarker)).toBe('retained')
})

test('namespace pages support native links and search without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false })
    const page = await context.newPage()
    try {
        await page.goto('/plugins/markstory')
        await page.getByText('Browse categories', { exact: true }).click()
        await page.getByRole('link', { name: 'Asset Management, 1 plugin', exact: true }).click()
        await expect(page).toHaveURL(/\/plugins\/markstory\?.*category=asset-management/)
        await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('no-match')
        await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).press('Enter')
        await expect(page).toHaveURL(/\/plugins\/markstory\?/)
        await expect(rows(page)).toHaveCount(0)
        await expect(stats(page).locator('[data-namespace-stat="plugins"]')).toHaveText('1')
        await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
        await expect(rows(page)).toHaveCount(1)
        await page.goto('/plugins/vendor')
        await page.getByRole('link', { name: 'Last', exact: true }).click()
        await expect(page).toHaveURL('/plugins/vendor?page=2')
        await expect(rows(page)).toHaveCount(2)
    } finally {
        await context.close()
    }
})

test('namespace profile loads lazily without moving the hero or blocking the catalogue', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        await gate
        await route.continue()
    }, { times: 1 })
    const response = await page.goto('/plugins/vendor')
    expect(await response.text()).toContain('data-namespace-profile-loading')
    const banner = page.locator('[data-namespace-banner]')
    let loadingBounds
    try {
        await expect(hero(page).getByRole('heading', { level: 1 })).toHaveText('vendor')
        await expect(hero(page).locator('.skeleton')).toBeVisible()
        await expect(hero(page).locator('[data-namespace-description]')).toHaveText('Plugins for your CakePHP projects.')
        await expect(rows(page)).toHaveCount(21)
        loadingBounds = await banner.boundingBox()
    } finally {
        release()
    }
    const avatar = hero(page).locator('[data-namespace-avatar]')
    await expect(avatar).toBeVisible()
    await expect(hero(page).locator('[data-namespace-description]')).toHaveText('CakePHP plugins by vendor.')
    await expect(hero(page).locator('.skeleton')).toHaveCount(0)
    expect(await banner.boundingBox()).toEqual(loadingBounds)
    await avatar.evaluate(element => { element.avatarMarker = 'retained' })
    await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('package-01')
    await expect(rows(page)).toHaveCount(1)
    expect(await avatar.evaluate(element => element.avatarMarker)).toBe('retained')
})

test('a failed avatar leaves readable initials and the generic description', async ({ page }) => {
    await page.route('https://avatars.githubusercontent.com/**', route => route.abort())
    await page.goto('/plugins/cakedc')
    await expect(page.locator('[data-namespace-profile]')).toBeVisible()
    await expect(hero(page).locator('[data-namespace-avatar]')).toBeHidden()
    await expect(hero(page).locator('.namespace-avatar')).toContainText('CA')
    await expect(hero(page).locator('[data-namespace-description]')).toHaveText('Plugins for your CakePHP projects.')
})

test('a failed profile mount keeps the hero stable and retries without blocking search', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        await gate
        await route.abort()
    }, { times: 1 })
    await page.goto('/plugins/vendor')
    const banner = page.locator('[data-namespace-banner]')
    const loadingBounds = await banner.boundingBox()
    release()
    const retry = hero(page).getByRole('button', { name: 'Retry profile', exact: true })
    await expect(retry).toBeVisible()
    await expect(hero(page).locator('.skeleton')).toBeHidden()
    await expect(hero(page).locator('[data-namespace-avatar]')).toBeVisible()
    expect(await banner.boundingBox()).toEqual(loadingBounds)
    await page.evaluate(() => { window.profileRecoveryMarker = 'retained' })
    await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('package-01')
    await expect(rows(page)).toHaveCount(1)
    await page.screenshot({ path: 'tmp/namespace-profile-retry.png', animations: 'disabled' })
    await retry.click()
    await expect(page.locator('[data-namespace-profile]')).toBeVisible()
    await expect(hero(page).locator('[data-namespace-description]')).toHaveText('CakePHP plugins by vendor.')
    await expect(rows(page)).toHaveCount(1)
    expect(await page.evaluate(() => window.profileRecoveryMarker)).toBe('retained')
    expect(await banner.boundingBox()).toEqual(loadingBounds)
})

for (const [code, status] of [['snapshot_expired', 409], ['csrf_failed', 403]]) {
    test(`${code} profile failures reload natively and retain filters`, async ({ page }) => {
        await page.route('**/_interact/update', route => route.fulfill({
            status,
            contentType: 'application/json',
            headers: { Interact: 'true' },
            body: JSON.stringify({ version: 1, error: { code, message: 'A fresh profile request is required.' } }),
        }), { times: 1 })
        await page.setViewportSize({ width: 320, height: 844 })
        await page.goto('/plugins/vendor?search=package-01&sort=monthly_downloads')
        const reload = hero(page).getByRole('link', { name: 'Reload page', exact: true })
        await expect(reload).toBeVisible()
        await expect(hero(page).getByRole('button', { name: 'Retry profile', exact: true })).toBeHidden()
        await expect(hero(page).locator('[data-namespace-avatar]')).toBeVisible()
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
        await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('package-02')
        await expect(rows(page)).toHaveCount(1)
        await expect(rows(page).first()).toContainText('vendor/package-02')
        await page.screenshot({ path: `tmp/namespace-profile-${code}.png`, animations: 'disabled' })
        const origin = page.url()
        const navigation = page.waitForResponse(response => response.request().isNavigationRequest() && response.url() === origin)
        await reload.click()
        await navigation
        await expect(page.locator('[data-namespace-profile]')).toBeVisible()
        await expect(page).toHaveURL(origin)
        await expect(rows(page)).toHaveCount(1)
        await expect(hero(page).locator('[data-namespace-description]')).toHaveText('CakePHP plugins by vendor.')
    })
}

test('namespace counters count up in sequence without shifting the layout', async ({ page }) => {
    await page.clock.install({ time: new Date('2026-01-01T00:00:00Z') })
    await page.clock.pauseAt(new Date('2026-01-01T00:00:01Z'))
    await page.goto('/plugins/vendor')
    const plugins = stats(page).locator('[data-namespace-stat="plugins"]')
    const downloads = stats(page).locator('[data-namespace-stat="downloads"]')
    const stars = stats(page).locator('[data-namespace-stat="stars"]')
    const counterBounds = () => page.locator('[data-namespace-hero], [data-namespace-stats], [data-namespace-stat]').evaluateAll(elements => elements.map(element => {
        const { x, y, width, height } = element.getBoundingClientRect()
        return { x, y, width, height }
    }))
    await page.clock.runFor(100)
    // Profile hydration is independent of the counters and can briefly hide its hero.
    await expect(page.locator('[data-namespace-profile]')).toBeVisible()
    const initialBounds = await counterBounds()
    expect(Number(await plugins.textContent())).toBeGreaterThan(0)
    await expect(downloads).toHaveText('0')
    await expect(stars).toHaveText('0')
    await page.clock.runFor(200)
    expect(await downloads.textContent()).not.toBe('0')
    await expect(stars).toHaveText('0')
    expect(await counterBounds()).toEqual(initialBounds)
    await page.clock.runFor(300)
    expect(await stars.textContent()).not.toBe('0')
    expect(await counterBounds()).toEqual(initialBounds)
    await expect(downloads).toHaveAttribute('aria-label', '22,701')
    await page.clock.runFor(1200)
    await expect(downloads).toHaveText('22.7k')
    await expect(plugins).toHaveText('23')
    await expect(stars).toHaveText('2k')
    expect(await counterBounds()).toEqual(initialBounds)
})

test('namespace hero fits mobile screens in both themes', async ({ page, context }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' })
    for (const width of [390, 320]) {
        await page.setViewportSize({ width, height: 844 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await context.addCookies([{ name: 'theme', value: theme, domain: '127.0.0.1', path: '/' }])
            await page.goto('/plugins/dereuromark')
            await expect(page.locator('html')).toHaveAttribute('data-theme', theme)
            await expect(hero(page)).toBeVisible()
            await expect(stats(page).locator('[data-namespace-stat="downloads"]')).toHaveText('20k')
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true)
            await page.screenshot({ path: `tmp/namespace-mobile-${theme}-${width}.png`, fullPage: true, animations: 'disabled' })
            expect(await stats(page).locator('[data-namespace-stat]').evaluateAll(elements => elements.every(element => element.scrollWidth <= element.clientWidth))).toBe(true)
            const banner = page.locator('[data-namespace-banner]')
            expect(await banner.evaluate(element => ['::before', '::after'].every(pseudo => getComputedStyle(element, pseudo).animationName === 'none'))).toBe(true)
        }
    }
})

test('namespace URLs canonicalize casing and unknown namespaces return 404', async ({ page }) => {
    await page.goto('/plugins/Dereuromark?search=tools&sort=stars')
    await expect(page).toHaveURL('/plugins/dereuromark?search=tools&sort=stars')
    await expect(rows(page)).toHaveCount(1)
    const response = await page.goto('/plugins/unknown-namespace')
    expect(response.status()).toBe(404)
})
