import { test, expect } from '@playwright/test'

for (const width of [390, 1280]) {
    test(`catalogue renders its skeleton before loading without moving filters at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 844 })
        let release
        const gate = new Promise(resolve => { release = resolve })
        await page.route('**/_interact/update', async route => {
            await gate
            await route.continue()
        })
        await page.goto('/')
        let filterBounds
        try {
            const loading = page.locator('[data-catalogue-loading]')
            await expect(loading).toBeVisible()
            await expect(loading.locator('.skeleton').first()).toBeVisible()
            await expect(page.locator('.site-header')).toBeVisible()
            filterBounds = await loading.locator('.catalogue-discovery').boundingBox()
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
            await page.evaluate(() => { window.catalogueMarker = 'retained' })
            await page.screenshot({ path: `tmp/catalogue-skeleton-${width}.png`, animations: 'disabled' })
        } finally {
            release()
        }
        await expect(page.getByRole('searchbox', { name: 'Search plugins', exact: true })).toBeVisible()
        await expect(page.locator('[data-catalogue-loading]')).toHaveCount(0)
        await expect(page.locator('[data-package-row]')).toHaveCount(21)
        expect(await page.locator('.catalogue-discovery').boundingBox()).toEqual(filterBounds)
        expect(await page.evaluate(() => window.catalogueMarker)).toBe('retained')
    })
}

test('a failed lazy load retries while retaining the URL selections', async ({ page }) => {
    const errors = []
    page.on('pageerror', error => errors.push(error.message))
    await page.route('**/_interact/update', route => route.abort('failed'), { times: 1 })
    await page.goto('/?search=users&sort=stars')
    const loading = page.locator('[data-catalogue-loading]')
    await expect(loading.getByRole('alert')).toContainText('We couldn’t load the plugins.')
    await loading.getByRole('button', { name: 'Retry', exact: true }).click()
    await expect(page.locator('[data-package-row]')).toHaveCount(1)
    await expect(page.locator('[data-package-row]')).toContainText('cakedc/users')
    await expect(page.getByRole('searchbox', { name: 'Search plugins', exact: true })).toHaveValue('users')
    expect(errors).toEqual([])
})

test('navigation to the catalogue displays the placeholder before its results load', async ({ page }) => {
    await page.goto('/publishers')
    await page.evaluate(() => { window.navigationMarker = 'retained' })
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        await gate
        await route.continue()
    })
    try {
        await page.locator('.desktop-nav').getByRole('link', { name: 'Browse plugins', exact: true }).click()
        await expect(page).toHaveURL('/')
        await expect(page.locator('[data-catalogue-loading]')).toBeVisible()
        await expect(page.locator('[data-catalogue-loading] .skeleton').first()).toBeVisible()
        expect(await page.evaluate(() => window.navigationMarker)).toBe('retained')
    } finally {
        release()
    }
    await expect(page.locator('[data-package-row]')).toHaveCount(21)
})
