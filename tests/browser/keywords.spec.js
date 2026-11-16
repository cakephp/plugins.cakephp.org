import { test, expect } from '@playwright/test'

const rows = page => page.locator('[data-package-row]')
const search = page => page.getByRole('searchbox', { name: 'Search plugins', exact: true })
const keywordChip = page => page.getByRole('link', { name: 'Remove keyword filter: sass', exact: true })

test('search finds keyword-only matches and exact filters do not match prefixes', async ({ page }) => {
    await page.goto('/')
    await expect(search(page)).toHaveAttribute('placeholder', 'Search by name, description or keyword')
    await search(page).fill('sass')
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('markstory/asset_compress')
    await page.goto('/?keyword=sas')
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
    await expect(page.locator('.selected-keyword')).toHaveText('Keyword: sas ×')
})

test('modal keyword links preserve filters and update the catalogue without navigation', async ({ page }) => {
    await page.goto('/?search=asset&category=asset-management&awesome=1&featured=1&sort=stars&direction=desc&cakephp_slugs[]=cakephp-5-4')
    await expect(rows(page)).toHaveCount(1)
    await page.evaluate(() => { window.documentMarker = 'retained' })
    await rows(page).getByRole('link', { name: /View .* details/ }).click()
    const dialog = page.getByRole('dialog')
    const keywords = dialog.getByRole('region', { name: 'Keywords', exact: true })
    await expect(keywords.getByRole('link', { name: 'sass', exact: true })).toHaveAttribute('href', '/?keyword=sass')
    await expect(keywords.getByRole('link', { name: 'cakephp', exact: true })).toHaveCount(0)
    await keywords.getByRole('link', { name: 'sass', exact: true }).click()
    await expect(dialog).not.toBeVisible()
    await expect(keywordChip(page)).toBeVisible()
    const params = new URL(page.url()).searchParams
    for (const [key, value] of Object.entries({ keyword: 'sass', search: 'asset', category: 'asset-management', awesome: '1', featured: '1', sort: 'stars' })) {
        expect(params.get(key)).toBe(value)
    }
    await expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
    await keywordChip(page).click()
    await expect(page).not.toHaveURL(/keyword=/)
    await expect(page).toHaveURL(/category=asset-management/)
    await expect(page).toHaveURL(/sort=stars/)
})

test('keyword URLs support history, reloads, form fields and reset', async ({ page }) => {
    await page.goto('/?sort=downloads&direction=asc')
    await expect(rows(page)).toHaveCount(21)
    await page.getByRole('link', { name: 'Last', exact: true }).click()
    await expect(page).toHaveURL(/page=2/)
    await rows(page).filter({ hasText: 'markstory/asset_compress' }).getByRole('link', { name: /View .* details/ }).click()
    await page.getByRole('dialog').getByRole('link', { name: 'sass', exact: true }).click()
    await expect(keywordChip(page)).toBeVisible()
    await expect(page).not.toHaveURL(/page=2/)
    await page.goBack()
    await expect(page).toHaveURL(/page=2/)
    await expect(keywordChip(page)).toHaveCount(0)
    await page.goForward()
    await expect(keywordChip(page)).toBeVisible()
    await page.reload()
    await expect(keywordChip(page)).toBeVisible()
    await expect(page.locator('#catalogue-search-form input[name="keyword"]')).toHaveValue('sass')
    await search(page).fill('users')
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(page).not.toHaveURL(/keyword=|search=/)
    await expect(page).toHaveURL(/sort=downloads/)
})

test('namespace modal selection retains its scope and standalone links open the directory', async ({ page }) => {
    await page.goto('/plugins/markstory')
    await rows(page).getByRole('link', { name: /View .* details/ }).click()
    await page.getByRole('dialog').getByRole('link', { name: 'sass', exact: true }).click()
    await expect(page).toHaveURL(/\/plugins\/markstory\?keyword=sass/)
    await expect(rows(page)).toHaveCount(1)
    await page.goto('/plugins/markstory/asset_compress')
    await page.getByRole('region', { name: 'Keywords', exact: true }).getByRole('link', { name: 'sass', exact: true }).click()
    await expect(page).toHaveURL(/\/\?keyword=sass/)
    await expect(keywordChip(page)).toBeVisible()
    await page.goto('/plugins/vendor/package-02')
    await expect(page.getByRole('region', { name: 'Keywords', exact: true })).toHaveCount(0)
})

test('keyword links support keyboard activation and fit both themes on narrow screens', async ({ page }) => {
    for (const width of [320, 390, 1280]) {
        await page.setViewportSize({ width, height: 844 })
        await page.goto('/plugins/markstory/asset_compress')
        const keywords = page.getByRole('region', { name: 'Keywords', exact: true })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await page.evaluate(theme => { document.documentElement.dataset.theme = theme }, theme)
            await expect(keywords).toBeVisible()
            expect(await keywords.evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true)
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
        }
        await keywords.getByRole('link', { name: 'sass', exact: true }).focus()
        await page.keyboard.press('Enter')
        await expect(keywordChip(page)).toBeVisible()
        await page.goto(`/?keyword=${'a'.repeat(191)}`)
        await expect(page.locator('.selected-keyword')).toBeVisible()
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
    }
})
