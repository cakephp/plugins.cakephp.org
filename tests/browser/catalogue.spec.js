import { test, expect } from '@playwright/test'

const pageErrors = new WeakMap()

test.beforeEach(async ({ page }) => {
    const errors = []
    pageErrors.set(page, errors)
    page.on('pageerror', error => errors.push(error.message))
    await page.goto('/')
    await expect(page.locator('body')).not.toHaveAttribute('x-cloak')
    await page.waitForFunction(() => !!window.Alpine?.store('interactNavigation'))
    await page.evaluate(() => { window.documentMarker = 'retained' })
})

test.afterEach(async ({ page }) => {
    expect(pageErrors.get(page)).toEqual([])
})

test('production assets use hashed URLs and Interact reload tracking', async ({ page }) => {
    const script = page.locator('script[type="module"]')
    const stylesheet = page.locator('link[rel="stylesheet"]')
    await expect(script).toHaveAttribute('x-interact:asset', 'reload')
    await expect(stylesheet).toHaveAttribute('x-interact:asset', 'reload')
    await expect(script).toHaveAttribute('src', /^\/build\/assets\/app-[\w-]+\.js$/)
    await expect(stylesheet).toHaveAttribute('href', /^\/build\/assets\/style-[\w-]+\.css$/)
    await expect(page.locator('script[src*="/interact/js/runtime.js"]')).toHaveCount(1)
    await expect(page.locator('script[src*="@vite/client"]')).toHaveCount(0)
    expect(await page.evaluate(async () => {
        await document.fonts.load('16px Raleway')
        return document.fonts.check('16px Raleway')
    })).toBe(true)
})

test('sorting, pagination, and history update the catalogue in place', async ({ page }) => {
    const catalogue = page.locator('#packages-index-content')
    await expect(page.locator('[data-featured-packages-slider]')).toBeVisible()
    await catalogue.getByRole('link', { name: 'Downloads', exact: true }).click()
    await expect(page).toHaveURL(/sort=downloads/)
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
    await catalogue.getByRole('link', { name: 'next ›' }).last().click()
    await expect(page).toHaveURL(/page=2/)
    await expect(page.locator('[data-featured-packages-slider]')).toHaveCount(0)
    await page.goBack()
    await expect(page).not.toHaveURL(/page=2/)
    await expect(page.locator('[data-featured-packages-slider]')).toBeVisible()
    await expect(page.getByRole('group', { name: 'CakePHP version' })).toBeVisible()
    await expect(page.getByRole('group', { name: 'PHP version', exact: true })).toBeVisible()
})

test('featured island survives sorting and its compatibility links filter the catalogue', async ({ page }) => {
    const slider = page.locator('[data-featured-packages-slider]')
    await expect(slider).toBeVisible()
    const islandId = await slider.evaluate(element => {
        element.featuredMarker = 'retained'
        return element.closest('[data-interact-id]').dataset.interactId
    })
    const catalogueId = await page.locator('#packages-index-content').evaluate(element =>
        element.closest('[data-interact-id]').dataset.interactId)
    expect(islandId).not.toBe(catalogueId)

    await page.locator('#packages-index-content').getByRole('link', { name: 'Downloads', exact: true }).click()
    await expect(page).toHaveURL(/sort=downloads/)
    expect(await slider.evaluate(element => element.closest('[data-interact-id]').dataset.interactId)).toBe(islandId)
    expect(await slider.evaluate(element => element.featuredMarker)).toBe('retained')
    expect(await slider.evaluate(element => !!element.swiper && !element.swiper.destroyed)).toBe(true)

    await slider.locator('.swiper-slide-active article').getByRole('button', { name: /^[45]\.x$/ }).first().click()
    await page.locator('dialog[open] a[data-version-group="cakephp_slugs"]').first().click()
    await expect(page).toHaveURL(/cakephp_slugs/)
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(slider).toHaveCount(0)
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
})

test('autocomplete renders suggestions and Enter performs enhanced search', async ({ page }) => {
    const search = page.getByRole('combobox', { name: 'Search packages' })
    await search.fill('asset')
    await expect(page.getByRole('option', { name: /markstory\/asset_compress/ })).toBeVisible()
    await search.press('ArrowDown')
    await expect(search).toHaveAttribute('aria-activedescendant', 'suggestion-0')
    await search.press('Escape')
    await expect(search).toHaveAttribute('aria-expanded', 'false')
    await search.press('Enter')
    await expect(page).toHaveURL(/search=asset/)
    await expect(page.locator('#packages-index-content')).toContainText('markstory/asset_compress')
    await expect(page.locator('[data-featured-packages-slider]')).toHaveCount(0)
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
})

test('navigation preserves theme and allows searching from Requirements', async ({ page, context }) => {
    await page.locator('#theme-toggle').click()
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    const cookie = (await context.cookies()).find(cookie => cookie.name === 'theme')
    expect(cookie).toMatchObject({ value: 'cakephp-dark', httpOnly: true, sameSite: 'Lax', path: '/' })
    expect(cookie.expires).toBeGreaterThan(Date.now() / 1000 + 300 * 86400)
    await page.getByRole('link', { name: 'Requirements', exact: true }).first().click()
    await expect(page).toHaveURL(/\/requirements$/)
    await expect(page.getByRole('heading', { name: 'Minimum requirements for being listed' })).toBeVisible()
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    const search = page.getByRole('combobox', { name: 'Search packages' })
    await search.fill('users')
    await search.press('Enter')
    await expect(page).toHaveURL(/search=users/)
    await expect(page.locator('#packages-index-content')).toContainText('cakedc/users')
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
})

test('page navigation shows the global top loading bar', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/requirements', async route => {
        await gate
        await route.continue()
    })
    const loading = page.getByRole('progressbar', { name: 'Loading page' })
    await expect(loading).toBeHidden()
    try {
        await page.getByRole('link', { name: 'Requirements', exact: true }).first().click()
        await expect(loading).toBeVisible()
        expect(await loading.evaluate(element => element.getBoundingClientRect().top)).toBe(0)
    } finally {
        release()
    }
    await expect(page).toHaveURL(/\/requirements$/)
    await expect(loading).toBeHidden()
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
})

test('version pills filter immediately and search preserves the selection', async ({ page }) => {
    await page.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(page).toHaveURL(/cakephp_slugs/)
    await expect(page.locator('#packages-index-content')).toContainText('cakedc/users')
    await expect(page.locator('#packages-index-content')).not.toContainText('markstory/asset_compress')
    await page.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(page).not.toHaveURL(/cakephp_slugs/)
    await expect(page.locator('[data-featured-packages-slider]')).toBeVisible()
    await page.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(page).toHaveURL(/cakephp_slugs/)
    const search = page.getByRole('combobox', { name: 'Search packages' })
    await search.fill('users')
    await search.press('Enter')
    await expect(page).toHaveURL(/search=users/)
    await expect(page).toHaveURL(/cakephp_slugs/)
    await expect(page.getByRole('button', { name: 'CakePHP 4.4', exact: true })).toHaveAttribute('aria-pressed', 'true')
    await expect(page.getByRole('group', { name: 'CakePHP version' })).toBeVisible()
    await expect(page.getByRole('group', { name: 'PHP version', exact: true })).toBeVisible()
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
})

test('search shows immediate feedback until suggestions arrive', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        await gate
        await route.continue()
    })
    const search = page.getByRole('combobox', { name: 'Search packages' })
    const request = page.waitForRequest('**/_interact/update')
    try {
        await search.fill('asset')
        await expect(page.getByRole('status', { name: 'Loading suggestions' })).toBeVisible()
        await request
        await expect(search).toHaveAttribute('aria-busy', 'true')
    } finally {
        release()
    }
    await expect(page.getByRole('option', { name: /markstory\/asset_compress/ })).toBeVisible()
    await expect(page.getByRole('status', { name: 'Loading suggestions' })).toBeHidden()
    await expect(search).toHaveAttribute('aria-busy', 'false')
})

test('rapid input settles on current suggestions', async ({ page }) => {
    const search = page.getByRole('combobox', { name: 'Search packages' })
    await search.fill('asset')
    await expect(page.getByRole('option', { name: /markstory\/asset_compress/ })).toBeVisible()
    await search.fill('package')
    await search.fill('users')
    await expect(page.getByRole('option', { name: /cakedc\/users/ })).toBeVisible()
    await expect(page.getByRole('option', { name: /markstory\/asset_compress/ })).toHaveCount(0)
})

test('GET forms and paginator links work without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false })
    const page = await context.newPage()
    await page.goto('http://127.0.0.1:8765/')
    await page.getByRole('combobox', { name: 'Search packages' }).fill('users')
    await page.getByRole('combobox', { name: 'Search packages' }).press('Enter')
    await expect(page).toHaveURL(/search=users/)
    await expect(page.locator('#packages-index-content')).toContainText('cakedc/users')
    await page.goto('http://127.0.0.1:8765/')
    await page.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(page).toHaveURL(/cakephp_slugs/)
    await expect(page.locator('#packages-index-content')).toContainText('cakedc/users')
    await page.goto('http://127.0.0.1:8765/')
    await page.locator('#packages-index-content').getByRole('link', { name: 'next ›' }).last().click()
    await expect(page).toHaveURL(/page=2/)
    await context.close()
})

test('theme cookie renders before JavaScript and both toggles share the preference', async ({ page, context, browser }) => {
    await page.locator('#theme-toggle').click()
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
    const response = await page.reload()
    expect(await response.text()).toContain('data-theme="cakephp-dark"')
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    expect(await page.evaluate(() => localStorage.getItem('theme'))).toBeNull()

    const noScript = await browser.newContext({ javaScriptEnabled: false, storageState: await context.storageState() })
    try {
        const document = await noScript.newPage()
        await document.goto('http://127.0.0.1:8765/requirements')
        await expect(document.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    } finally {
        await noScript.close()
    }

    await page.setViewportSize({ width: 390, height: 844 })
    await page.getByLabel('Open menu').click()
    await expect(page.locator('#theme-toggle-mobile')).toContainText('Light mode')
    await page.locator('#theme-toggle-mobile').click()
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp')
    await page.setViewportSize({ width: 1280, height: 900 })
    await page.locator('#theme-toggle').click()
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
})
