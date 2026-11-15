import { test, expect } from '@playwright/test'

const rows = page => page.locator('[data-package-row]')
const hero = page => page.locator('[data-namespace-hero]')
const stats = page => page.getByRole('region', { name: 'Namespace statistics', exact: true })
const pageErrors = new WeakMap()
const targetsProfile = request => {
    const snapshot = request.postDataJSON()?.snapshot
    if (typeof snapshot !== 'string') return false
    return JSON.parse(Buffer.from(snapshot.split('.')[0], 'base64url').toString()).data.alias === 'NamespaceProfile'
}
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

test('namespace hero spans the page and centers its identity and counters', async ({ page }) => {
    await page.goto('/plugins/vendor')
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('vendor')
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
    await page.screenshot({ path: 'tmp/namespace-desktop.png', animations: 'disabled' })
})

test('search, version filters, sorting and reset retain the namespace and hero', async ({ page }) => {
    await page.goto('/plugins/vendor')
    await page.evaluate(() => { window.namespaceMarker = 'retained' })
    await chooseSort(page, 'Downloads')
    await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('package-01')
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('vendor/package-01')
    await page.locator('#versions-cakephp_slugs > summary').click()
    await page.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(rows(page)).toHaveCount(0)
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
    await expect(stats(page).locator('[data-namespace-stat="plugins"]')).toHaveText('23')
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'noindex,follow')
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
    await page.locator('#catalogue-categories > summary').click()
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
    await expect(page.locator('[data-community-pick]').getByRole('link', { name: 'View cakedc/users details', exact: true }))
        .toHaveAttribute('href', '/plugins/cakedc/users')
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

test('namespace profile loads lazily without moving the hero or blocking the catalogue', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        if (!targetsProfile(route.request())) return route.continue()
        await gate
        await route.continue()
    })
    await page.goto('/plugins/vendor')
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
    let failed = false
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        if (!targetsProfile(route.request()) || failed) return route.continue()
        failed = true
        await gate
        await route.abort()
    })
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
        let failed = false
        await page.route('**/_interact/update', route => {
            if (!targetsProfile(route.request()) || failed) return route.continue()
            failed = true
            return route.fulfill({
                status,
                contentType: 'application/json',
                headers: { Interact: 'true' },
                body: JSON.stringify({ version: 1, error: { code, message: 'A fresh profile request is required.' } }),
            })
        })
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
