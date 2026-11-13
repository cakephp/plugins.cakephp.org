import { test, expect } from '@playwright/test'
import { execFileSync } from 'node:child_process'

const rows = page => page.locator('[data-publisher-namespace]')
const names = page => rows(page).evaluateAll(elements => elements.map(element => element.dataset.publisherNamespace))
const search = page => page.getByRole('searchbox', { name: 'Search publishers', exact: true })
const sort = page => page.getByRole('combobox', { name: 'Rank publishers by', exact: true })
const version = page => page.getByRole('combobox', { name: 'CakePHP version', exact: true })
const errors = new WeakMap()

test.beforeEach(async ({ page }) => {
    const messages = []
    page.on('pageerror', error => messages.push(error.message))
    errors.set(page, messages)
    await page.route('https://github.com/*.png*', route => route.fulfill({
        contentType: 'image/png',
        path: 'webroot/img/packagist-logo.png',
    }))
    await page.route('https://avatars.githubusercontent.com/**', route => route.fulfill({
        contentType: 'image/png',
        path: 'webroot/img/packagist-logo.png',
    }))
})

test.afterEach(async ({ page }) => {
    expect(errors.get(page)).toEqual([])
})

test('directory ranks publishers with totals and two popular plugins', async ({ page }) => {
    let islandUpdates = 0
    page.on('request', request => {
        if (request.url().endsWith('/_interact/update')) islandUpdates++
    })
    await page.goto('/publishers')
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Publishers')
    await expect.poll(() => names(page)).toEqual(['vendor', 'dereuromark', 'markstory'])
    await expect(page.locator('.desktop-nav').getByRole('link', { name: 'Publishers', exact: true })).toHaveAttribute('aria-current', 'page')
    const vendor = rows(page).first()
    await expect(vendor.locator('[data-publisher-rank]')).toHaveText('#1')
    await expect(vendor.locator('[data-publisher-stat="plugins"]')).toHaveText('23')
    await expect(vendor.locator('[data-publisher-stat="monthly_downloads"]')).toHaveText('2k')
    await expect(vendor.locator('[data-publisher-stat="monthly_downloads"]')).toHaveAttribute('aria-label', '2,001')
    await expect(vendor.locator('[data-publisher-stat="stars"]')).toHaveText('2k')
    await expect(vendor.getByRole('list', { name: 'Popular plugins by vendor' }).getByRole('link')).toHaveText(['package-01', 'package-02'])
    await expect(vendor.locator('[data-publisher-avatar]')).toHaveJSProperty('naturalWidth', 100)
    expect(islandUpdates).toBe(0)
    await page.screenshot({ path: 'tmp/publishers-desktop.png', fullPage: true })
})

test('live search and sorting preserve URL state and browser history', async ({ page }) => {
    await page.goto('/publishers')
    await page.evaluate(() => { window.publisherMarker = 'retained' })
    await sort(page).selectOption('stars')
    await expect(page).toHaveURL('/publishers?sort=stars')
    await expect.poll(() => names(page)).toEqual(['vendor', 'markstory', 'dereuromark'])
    await version(page).selectOption('all')
    await expect(page).toHaveURL('/publishers?sort=stars&cakephp=all')
    await expect.poll(() => names(page)).toEqual(['vendor', 'cakedc', 'markstory', 'dereuromark'])
    await search(page).fill('MARK')
    await expect(rows(page)).toHaveCount(2)
    await expect.poll(() => names(page)).toEqual(['markstory', 'dereuromark'])
    await expect(page).toHaveURL(/search=MARK/)
    await expect(page.locator('[data-publisher-rank]')).toHaveCount(0)
    await page.getByRole('link', { name: 'Clear search', exact: true }).click()
    await expect(page).toHaveURL('/publishers?sort=stars&cakephp=all')
    await expect(rows(page)).toHaveCount(4)
    await sort(page).selectOption('plugins')
    await expect(page).toHaveURL('/publishers?sort=plugins&cakephp=all')
    await expect.poll(() => names(page)).toEqual(['vendor', 'cakedc', 'dereuromark', 'markstory'])
    await page.goBack()
    await expect(page).toHaveURL('/publishers?sort=stars&cakephp=all')
    await expect.poll(() => names(page)).toEqual(['vendor', 'cakedc', 'markstory', 'dereuromark'])
    expect(await page.evaluate(() => window.publisherMarker)).toBe('retained')
    await page.reload()
    await expect(sort(page)).toHaveValue('stars')
    await search(page).fill('no-match')
    await expect(page.getByRole('heading', { name: 'No publishers found' })).toBeVisible()
})

test('directory navigation and publisher and plugin links use Interact', async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => { window.publisherMarker = 'retained' })
    await page.locator('.desktop-nav').getByRole('link', { name: 'Publishers', exact: true }).click()
    await expect(page).toHaveURL('/publishers')
    await rows(page).first().getByRole('link', { name: 'vendor', exact: true }).click()
    await expect(page).toHaveURL('/plugins/vendor')
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('vendor')
    await page.getByRole('link', { name: '← Back', exact: true }).click()
    await expect(page).toHaveURL('/publishers')
    await rows(page).first().getByRole('link', { name: 'View vendor/package-01 details', exact: true }).click()
    await expect(page).toHaveURL('/plugins/vendor/package-01')
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('vendor/package-01')
    expect(await page.evaluate(() => window.publisherMarker)).toBe('retained')
})

test('publisher back link retains overview filters after searching within a profile', async ({ page }) => {
    await page.goto('/publishers?search=vendor&sort=stars&cakephp=all')
    await rows(page).first().getByRole('link', { name: 'vendor', exact: true }).click()
    await expect(page).toHaveURL('/plugins/vendor')
    await page.getByRole('searchbox', { name: 'Search plugins', exact: true }).fill('package-01')
    await expect(page.locator('[data-package-row]')).toHaveCount(1)
    await page.getByRole('link', { name: '← Back', exact: true }).click()
    await expect(page).toHaveURL('/publishers?search=vendor&sort=stars&cakephp=all')
    await expect(search(page)).toHaveValue('vendor')
    await expect(sort(page)).toHaveValue('stars')
    await expect(version(page)).toHaveValue('all')
    await expect(rows(page)).toHaveCount(1)
})

test('version selection scopes totals, previews and latest release without hiding older publishers', async ({ page }) => {
    await page.goto('/publishers')
    await expect(version(page)).toHaveValue('current')
    await expect(sort(page)).toHaveValue('monthly_downloads')
    await expect(page.locator('[data-publisher-scope]')).toContainText('CakePHP 5')
    await version(page).selectOption('4')
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page).first()).toHaveAttribute('data-publisher-namespace', 'cakedc')
    await expect(rows(page).first().locator('[data-publisher-stat="monthly_downloads"]')).toHaveText('1.2k')
    await expect(rows(page).first().locator('time')).toHaveAttribute('datetime', '2025-03-01')
    await expect(page.locator('[data-publisher-scope]')).toContainText('CakePHP 4')
    await sort(page).selectOption('downloads')
    await expect(rows(page).first().locator('[data-publisher-stat="downloads"]')).toHaveText('9k')
    await search(page).fill('cake')
    await expect(page.locator('[data-publisher-rank]')).toHaveCount(0)
    await page.getByRole('link', { name: 'Clear search', exact: true }).click()
    await expect(search(page)).toHaveValue('')
    await expect(page).not.toHaveURL(/search=/)
    await expect(version(page)).toHaveValue('4')
    await expect(sort(page)).toHaveValue('downloads')
    await page.reload()
    await expect(rows(page)).toHaveCount(1)
    await version(page).selectOption('all')
    await expect(rows(page)).toHaveCount(4)
    await page.goBack()
    await expect(rows(page)).toHaveCount(1)
    await expect(version(page)).toHaveValue('4')
})

test('pagination maintains ranks and sorting or searching resets to page one', async ({ page }) => {
    execFileSync('php', ['tests/browser/publishers-state.php', 'seed'])
    try {
        await page.goto('/publishers')
        await expect(rows(page)).toHaveCount(20)
        await page.getByRole('link', { name: 'Next', exact: true }).click()
        await expect(page).toHaveURL('/publishers?page=2')
        await expect(rows(page)).toHaveCount(4)
        await expect(rows(page).first().locator('[data-publisher-rank]')).toHaveText('#21')
        await page.goBack()
        await expect(rows(page)).toHaveCount(20)
        await page.goForward()
        await expect(rows(page)).toHaveCount(4)
        await page.reload()
        await expect(rows(page)).toHaveCount(4)
        await sort(page).selectOption('stars')
        await expect(page).toHaveURL('/publishers?sort=stars')
        await expect(rows(page)).toHaveCount(20)
        await page.getByRole('link', { name: 'Next', exact: true }).click()
        await expect(rows(page)).toHaveCount(4)
        await version(page).selectOption('4')
        await expect(rows(page)).toHaveCount(1)
        await expect(rows(page).first()).toHaveAttribute('data-publisher-namespace', 'cakedc')
        await expect(page).not.toHaveURL(/page=2/)
        await version(page).selectOption('current')
        await search(page).fill('vendor')
        await expect(rows(page)).toHaveCount(1)
        await expect(page).not.toHaveURL(/page=2/)
        await expect(page.locator('[data-publisher-rank]')).toHaveCount(0)
    } finally {
        execFileSync('php', ['tests/browser/publishers-state.php', 'clear'])
    }
})

test('native GET search, sorting and links work without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false })
    const page = await context.newPage()
    try {
        await page.goto('/publishers')
        await sort(page).selectOption('stars')
        await search(page).fill('MARK')
        await page.getByRole('button', { name: 'Apply', exact: true }).click()
        await expect(rows(page)).toHaveCount(2)
        await expect.poll(() => names(page)).toEqual(['markstory', 'dereuromark'])
        await page.getByRole('link', { name: 'markstory', exact: true }).click()
        await expect(page).toHaveURL('/plugins/markstory')
        await page.getByRole('link', { name: '← Back', exact: true }).click()
        await expect(rows(page)).toHaveCount(2)
        await expect(search(page)).toHaveValue('MARK')
        await expect(sort(page)).toHaveValue('stars')
        await page.goto('/publishers?cakephp=all')
        await page.getByRole('link', { name: 'View cakedc/users details', exact: true }).click()
        await expect(page).toHaveURL('/plugins/cakedc/users')
    } finally {
        await context.close()
    }
})

test('directory fits mobile screens in both themes and handles missing avatars', async ({ page, context }) => {
    await page.route('https://github.com/*.png*', route => route.abort())
    for (const width of [390, 320]) {
        await page.setViewportSize({ width, height: 844 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await context.addCookies([{ name: 'theme', value: theme, domain: '127.0.0.1', path: '/' }])
            await page.goto('/publishers')
            await expect(rows(page)).toHaveCount(3)
            await expect(rows(page).first().locator('[data-publisher-avatar]')).toBeHidden()
            await expect(rows(page).first().locator('.publisher-avatar')).toHaveText('VE')
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
            expect(await rows(page).evaluateAll(elements => elements.every(element => element.scrollWidth <= element.clientWidth))).toBe(true)
            await page.locator('.mobile-menu summary').click()
            await expect(page.getByRole('navigation', { name: 'Mobile navigation' }).getByRole('link', { name: 'Publishers', exact: true })).toHaveAttribute('aria-current', 'page')
            await page.locator('.mobile-menu summary').click()
            await page.screenshot({ path: `tmp/publishers-mobile-${theme}-${width}.png`, fullPage: true })
        }
    }
})
