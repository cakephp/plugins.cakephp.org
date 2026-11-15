import { test, expect } from '@playwright/test'

const pageErrors = new WeakMap()
const search = page => page.getByRole('searchbox', { name: 'Search plugins', exact: true })
const featured = page => page.locator('[data-package-row][data-community-pick]')
const rows = page => page.locator('[data-package-row]')
const retained = page => expect.poll(() => page.evaluate(() => window.documentMarker)).toBe('retained')
const chooseSort = async (page, label) => {
    await page.getByLabel('Sort plugins', { exact: true }).click()
    await page.locator('.sort-options').getByRole('link', { name: label, exact: true }).click()
}
const chooseCategory = async (page, label) => {
    await page.locator('#catalogue-categories > summary').click()
    await page.getByRole('group', { name: 'Plugin categories', exact: true }).getByRole('link', { name: label }).click()
}

test.beforeEach(async ({ page }) => {
    const errors = []
    pageErrors.set(page, errors)
    page.on('pageerror', error => errors.push(error.message))
    await page.goto('/')
    await page.waitForFunction(() => !!window.Alpine?.store('interactNavigation'))
    await expect(search(page)).toBeVisible()
    await page.evaluate(() => { window.documentMarker = 'retained' })
})

test.afterEach(async ({ page }) => {
    expect(pageErrors.get(page)).toEqual([])
})

test('ecosystem snapshot stays global while the catalogue is filtered', async ({ page }) => {
    const snapshot = page.getByRole('region', { name: 'Ecosystem snapshot', exact: true })
    const initialText = await snapshot.textContent()
    await snapshot.evaluate(element => { element.snapshotMarker = 'retained' })
    await search(page).fill('users')
    await expect(rows(page)).toHaveCount(1)
    await expect(snapshot).toHaveText(initialText)
    expect(await snapshot.evaluate(element => element.snapshotMarker)).toBe('retained')
})

test('ecosystem snapshot fits both themes on narrow and wide screens', async ({ page }) => {
    const snapshot = page.getByRole('region', { name: 'Ecosystem snapshot', exact: true })
    for (const width of [320, 390, 1280]) {
        await page.setViewportSize({ width, height: 844 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await page.evaluate(theme => { document.documentElement.dataset.theme = theme }, theme)
            await snapshot.scrollIntoViewIfNeeded()
            expect(await snapshot.evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true)
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
            const footer = await page.locator('footer').boundingBox()
            expect(footer.x).toBe(0)
            expect(footer.width).toBe(width)
        }
    }
})

test('category and Awesome filters combine live and reset without losing sort', async ({ page }) => {
    await expect(featured(page).locator('.awesome-badge')).toHaveCount(1)
    await page.getByRole('button', { name: 'Awesome only', exact: true }).click()
    await expect(page).toHaveURL(/awesome=1/)
    await expect(rows(page)).toHaveCount(2)
    await chooseCategory(page, 'Asset Management')
    await expect(page).toHaveURL(/category=asset-management/)
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('markstory/asset_compress')
    await expect(rows(page).locator('.awesome-badge')).toHaveText('Awesome CakePHP')
    await chooseSort(page, 'Downloads')
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(page).toHaveURL(/category=asset-management/)
    await page.locator('#versions-cakephp_slugs > summary').click()
    await page.getByRole('group', { name: 'CakePHP version', exact: true }).getByRole('button', { name: 'CakePHP 4.4' }).click()
    await expect(rows(page)).toHaveCount(0)
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(page).not.toHaveURL(/category=|awesome=|cakephp_slugs/)
    await expect(page.locator('.catalogue-toolbar')).toContainText('26 plugins')
    await retained(page)
})

test('category selection supports history, reloads and search fallback fields', async ({ page }) => {
    await page.getByRole('link', { name: 'Last', exact: true }).click()
    await expect(page).toHaveURL(/page=2/)
    await chooseCategory(page, 'Authentication & Authorization')
    await expect(page).toHaveURL(/category=authentication-authorization/)
    await expect(page).not.toHaveURL(/page=2/)
    await expect(rows(page)).toContainText('cakedc/users')
    await page.goBack()
    await expect(page).toHaveURL(/page=2/)
    await expect(page).not.toHaveURL(/category=/)
    await page.goForward()
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('cakedc/users')
    await page.reload()
    await expect(page.locator('.selected-category')).toHaveText('Authentication & Authorization ×')
    await expect(page.locator('input[type="hidden"][name="category"]')).toHaveValue('authentication-authorization')
})

test('category grid searches locally, supports keyboard dismissal and collapses on selection', async ({ page }) => {
    let updates = 0
    page.on('request', request => { if (request.url().includes('/_interact/update')) updates++ })
    const toggle = page.locator('#catalogue-categories > summary')
    await toggle.focus()
    await page.keyboard.press('Enter')
    const grid = page.getByRole('group', { name: 'Plugin categories', exact: true })
    await expect(grid).toBeVisible()
    await expect(grid.getByRole('link')).toHaveCount(4)
    await expect(grid.getByRole('link', { name: 'Asset Management, 1 plugin', exact: true })).toBeVisible()
    const categorySearch = page.getByRole('searchbox', { name: 'Find a category', exact: true })
    await categorySearch.fill('AUTH')
    await expect(grid.getByRole('link', { name: 'Asset Management' })).toBeHidden()
    await expect(grid.getByRole('link', { name: 'Authentication & Authorization' })).toBeVisible()
    await categorySearch.fill('no-such-category')
    await expect(page.getByRole('status').filter({ hasText: 'No matching categories' })).toBeVisible()
    expect(updates).toBe(0)
    await page.keyboard.press('Escape')
    await expect(grid).toBeHidden()
    await expect(toggle).toBeFocused()
    await toggle.click()
    await expect(categorySearch).toHaveValue('')
    await grid.getByRole('link', { name: 'Asset Management' }).click()
    await expect(page).toHaveURL(/category=asset-management/)
    await expect(grid).toBeHidden()
    await expect(toggle).toBeFocused()
})

for (const width of [390, 1280]) {
    test(`category dropdown overlays results and summarizes its selection at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 844 })
        const toggle = page.locator('#catalogue-categories > summary')
        const grid = page.getByRole('group', { name: 'Plugin categories', exact: true })
        const resultsTop = (await page.locator('#packages-index-content').boundingBox()).y
        await expect(toggle).toContainText('Category')
        await toggle.click()
        await expect(grid).toBeVisible()
        expect((await page.locator('#packages-index-content').boundingBox()).y).toBe(resultsTop)
        const panel = await page.locator('.category-browser-panel').boundingBox()
        expect(panel.x).toBeGreaterThanOrEqual(0)
        expect(panel.x + panel.width).toBeLessThanOrEqual(width)
        await search(page).click()
        await expect(grid).toBeHidden()
        await expect(search(page)).toBeFocused()
        await chooseCategory(page, 'Asset Management')
        await expect(toggle).toContainText('Asset Management')
        await expect(grid).toBeHidden()
        await toggle.click()
        await expect(grid.getByRole('link', { name: 'Asset Management' })).toHaveAttribute('aria-current', 'true')
        await page.locator('#versions-cakephp_slugs > summary').click()
        await expect(grid).toBeHidden()
        await retained(page)
    })
}

test('removing a category chip retains the Awesome filter and sort', async ({ page }) => {
    await chooseSort(page, 'Downloads')
    await page.getByRole('button', { name: 'Awesome only', exact: true }).click()
    await expect(rows(page)).toHaveCount(2)
    await chooseCategory(page, 'Asset Management')
    await expect(rows(page)).toHaveCount(1)
    await page.getByRole('link', { name: 'Remove category filter: Asset Management', exact: true }).click()
    await expect(page).not.toHaveURL(/category=/)
    await expect(page).toHaveURL(/awesome=1/)
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(rows(page)).toHaveCount(2)
    await expect(page.locator('.selected-category')).toHaveCount(0)
    await expect(page.locator('#catalogue-categories > summary')).toBeFocused()
    await retained(page)
})

test('long category names and curation labels fit narrow screens', async ({ page }) => {
    await chooseCategory(page, 'Authentication & Authorization')
    for (const width of [320, 390, 768]) {
        await page.setViewportSize({ width, height: 900 })
        await expect(rows(page)).toHaveCount(1)
        await expect(rows(page)).toContainText('cakedc/users')
        expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false)
    }
})

test('details category links retain directory selections and close the dialog', async ({ page }) => {
    await search(page).fill('asset')
    await expect(rows(page)).toHaveCount(1)
    await chooseSort(page, 'Downloads')
    await rows(page).locator('.package-name a').click()
    const dialog = page.locator('#plugin-modal')
    await expect(dialog.locator('.plugin-details')).toBeVisible()
    await expect(dialog.locator('.awesome-badge')).toHaveText('Awesome CakePHP')
    await expect(dialog.locator('.plugin-category-links a')).toHaveCount(2)
    await expect(dialog.getByRole('link', { name: 'Awesome CakePHP', exact: true })).toHaveAttribute('href', 'https://github.com/FriendsOfCake/awesome-cakephp')
    await dialog.getByRole('link', { name: 'Asset Management', exact: true }).click()
    await expect(dialog).not.toBeVisible()
    await expect(page).toHaveURL(/category=asset-management/)
    await expect(page).toHaveURL(/search=asset/)
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(rows(page)).toHaveCount(1)
    await retained(page)
})

test('catalogue explains its JavaScript requirement', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 390, height: 844 } })
    try {
        const page = await context.newPage()
        await page.goto('/')
        await expect(page.getByText('Enable JavaScript to search and browse plugins.', { exact: true })).toBeVisible()
        await expect(page.locator('[data-catalogue-skeleton]')).toBeVisible()
    } finally {
        await context.close()
    }
})

test('sorting, pagination, and history update results in place', async ({ page }) => {
    await expect(featured(page).first()).toBeVisible()
    await chooseSort(page, 'Downloads')
    await expect(page).toHaveURL(/sort=downloads/)
    await retained(page)
    await page.getByRole('link', { name: 'Next', exact: true }).click()
    await expect(page).toHaveURL(/page=2/)
    await expect(featured(page)).toHaveCount(0)
    await page.goBack()
    await expect(page).not.toHaveURL(/page=2/)
    await expect(featured(page).first()).toBeVisible()
    await page.getByRole('link', { name: 'Last', exact: true }).click()
    await expect(page).toHaveURL(/page=2/)
    await page.getByRole('link', { name: 'First', exact: true }).click()
    await expect(page).not.toHaveURL(/page=2/)
    await retained(page)
    await expect(page.getByRole('group', { name: 'CakePHP version', exact: true })).toBeVisible()
})

test('community picks keep normal ordering and a stable results position when filters reset', async ({ page }) => {
    await expect(page.locator('[data-featured-plugins]')).toHaveCount(0)
    await chooseSort(page, 'Downloads')
    await expect(featured(page)).toHaveCount(3)
    await expect(rows(page).first()).toContainText('dereuromark/cakephp-tools')
    const resultsTop = await page.locator('#packages-index-content').evaluate(element => element.getBoundingClientRect().top + scrollY)
    await search(page).fill('asset')
    await expect(rows(page)).toHaveCount(1)
    await expect(featured(page)).toHaveCount(1)
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(featured(page)).toHaveCount(3)
    await expect(rows(page)).toHaveCount(21)
    await expect(rows(page).first()).toContainText('dereuromark/cakephp-tools')
    expect(await page.locator('#packages-index-content').evaluate(element => element.getBoundingClientRect().top + scrollY)).toBeCloseTo(resultsTop, 1)
    await retained(page)
})

test('community-pick filter combines with search and category, survives reload and supports history', async ({ page }) => {
    const toggle = page.getByRole('button', { name: 'Community picks', exact: true })
    await toggle.click()
    await expect(toggle).toHaveAttribute('aria-pressed', 'true')
    await expect(page).toHaveURL(/featured=1/)
    await expect(rows(page)).toHaveCount(3)
    await chooseSort(page, 'Monthly downloads')
    await expect(rows(page).first()).toContainText('dereuromark/cakephp-tools')
    await chooseCategory(page, 'Asset Management')
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('markstory/asset_compress')
    await page.reload()
    await expect(toggle).toHaveAttribute('aria-pressed', 'true')
    await expect(rows(page)).toHaveCount(1)
    await search(page).fill('users')
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(toggle).toHaveAttribute('aria-pressed', 'false')
    await expect(page).not.toHaveURL(/featured=|category=|search=/)
    await page.goBack()
    await expect(toggle).toHaveAttribute('aria-pressed', 'true')
    await expect(page.getByText('No plugins found', { exact: true })).toBeVisible()
})

test('community picks fit both themes on mobile and desktop', async ({ page }) => {
    await page.getByRole('button', { name: 'Community picks', exact: true }).click()
    await expect(rows(page)).toHaveCount(3)
    for (const width of [320, 390, 1280]) {
        await page.setViewportSize({ width, height: 900 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await page.evaluate(theme => document.documentElement.dataset.theme = theme, theme)
            expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width)
            const row = rows(page).first()
            await expect(row.locator('.community-pick-badge')).toBeVisible()
            const bounds = await row.boundingBox()
            expect(bounds.x).toBeGreaterThanOrEqual(0)
            expect(bounds.x + bounds.width).toBeLessThanOrEqual(width)
            await page.screenshot({ path: `tmp/community-picks-${theme}-${width}.png`, fullPage: true, animations: 'disabled' })
        }
    }
})

test('live search updates results and replaces history while typing', async ({ page }) => {
    const historyLength = await page.evaluate(() => history.length)
    await search(page).fill('asset')
    await expect(page).toHaveURL(/search=asset/)
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('markstory/asset_compress')
    await expect(featured(page)).toHaveCount(1)
    await expect(search(page)).toBeFocused()
    await search(page).fill('users')
    await expect(page).toHaveURL(/search=users/)
    await expect(rows(page)).toContainText('cakedc/users')
    expect(await page.evaluate(() => history.length)).toBe(historyLength)
    await retained(page)
})

test('version filters are live and clearing retains the selected sort', async ({ page }) => {
    await chooseSort(page, 'Downloads')
    const cake = page.getByRole('group', { name: 'CakePHP version', exact: true })
    await cake.locator('summary').click()
    await cake.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(page).toHaveURL(/cakephp_slugs/)
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('cakedc/users')
    await search(page).fill('users')
    await expect(page).toHaveURL(/search=users/)
    await expect(cake.getByRole('button', { name: 'CakePHP 4.4', exact: true })).toHaveAttribute('aria-pressed', 'true')
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(page).not.toHaveURL(/cakephp_slugs|search=|page=/)
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(search(page)).toHaveValue('')
    await expect(featured(page).first()).toBeVisible()
    await retained(page)
})

test('version exclusions cycle with keyboard support and survive history and reloads', async ({ page }) => {
    const cake = page.getByRole('group', { name: 'CakePHP version', exact: true })
    const php = page.getByRole('group', { name: 'PHP version', exact: true })
    const oldVersion = cake.getByRole('button', { name: 'CakePHP 4.4', exact: true })
    await cake.locator('summary').click()
    await oldVersion.click()
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'include')
    await expect(rows(page)).toHaveCount(1)
    await oldVersion.press('Space')
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'exclude')
    await expect(cake.locator('summary')).toContainText('Not CakePHP 4.4')
    await expect(page).toHaveURL(/exclude_cakephp_slugs/)
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'noindex,follow')
    await expect(rows(page).filter({ hasText: 'cakedc/users' })).toHaveCount(0)
    await retained(page)
    await page.goBack()
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'include')
    await expect(rows(page)).toHaveCount(1)
    await page.goForward()
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'exclude')
    await page.reload()
    await expect(cake.locator('summary')).toContainText('Not CakePHP 4.4')
    await cake.locator('summary').click()
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'exclude')
    for (const width of [390, 1280]) {
        await page.setViewportSize({ width, height: 900 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await page.evaluate(theme => { document.documentElement.dataset.theme = theme }, theme)
            expect(await cake.locator('.version-dropdown-panel').evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true)
            await page.screenshot({ path: `tmp/filter-exclusions-versions-${theme}-${width}.png`, animations: 'disabled' })
        }
    }
    await oldVersion.press('Space')
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'neutral')
    await oldVersion.click()
    await oldVersion.click()
    await expect(oldVersion).toHaveAttribute('data-filter-state', 'exclude')
    await search(page).fill('package')
    await expect(page).toHaveURL(/search=package/)
    await expect(page.locator('input[type="hidden"][name="exclude_cakephp_slugs[]"]')).toHaveValue('cakephp-4-4')
    await php.locator('summary').click()
    const phpVersion = php.getByRole('button', { name: 'PHP 8.5', exact: true })
    await phpVersion.click()
    await expect(phpVersion).toHaveAttribute('data-filter-state', 'include')
    await phpVersion.click()
    await expect(phpVersion).toHaveAttribute('data-filter-state', 'exclude')
    await expect(rows(page)).toHaveCount(0)
    await cake.locator('summary').click()
    await cake.getByRole('link', { name: 'Clear CakePHP version', exact: true }).click()
    await expect(page).not.toHaveURL(/exclude_cakephp_slugs/)
    await expect(page).toHaveURL(/exclude_php_slugs/)
    await expect(rows(page)).toHaveCount(0)
    await page.getByRole('link', { name: 'Reset filters', exact: true }).click()
    await expect(page).not.toHaveURL(/exclude_|search=/)
    await expect(rows(page)).toHaveCount(21)
    await expect(page.locator('meta[name="robots"]')).toHaveCount(0)
})

test('category exclusions combine, remain visible and reset after history and reloads', async ({ page }) => {
    const toggle = page.locator('#catalogue-categories > summary')
    const grid = page.getByRole('group', { name: 'Plugin categories', exact: true })
    await chooseCategory(page, 'Asset Management')
    await chooseCategory(page, 'Asset Management')
    await expect(page).toHaveURL(/exclude_categories/)
    await expect(toggle).toContainText('Not Asset Management')
    await expect(rows(page).filter({ hasText: 'markstory/asset_compress' })).toHaveCount(0)
    await chooseCategory(page, 'Authentication & Authorization')
    await expect(rows(page)).toHaveCount(1)
    await chooseCategory(page, 'Authentication & Authorization')
    await expect(toggle).toContainText('2 excluded')
    await expect(rows(page).filter({ hasText: 'cakedc/users' })).toHaveCount(0)
    await page.goBack()
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('cakedc/users')
    await page.goForward()
    await expect(toggle).toContainText('2 excluded')
    await retained(page)
    await page.reload()
    await expect(toggle).toContainText('2 excluded')
    await toggle.click()
    await expect(grid.locator('[data-filter-state="exclude"]')).toHaveCount(2)
    for (const width of [390, 1280]) {
        await page.setViewportSize({ width, height: 900 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await page.evaluate(theme => { document.documentElement.dataset.theme = theme }, theme)
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
            await page.screenshot({ path: `tmp/filter-exclusions-categories-${theme}-${width}.png`, animations: 'disabled' })
        }
    }
    await grid.getByRole('link', { name: 'Asset Management' }).click()
    await expect(toggle).toContainText('Not Authentication & Authorization')
    await expect(page.locator('input[type="hidden"][name="exclude_categories[]"]')).toHaveValue('authentication-authorization')
    await toggle.click()
    await grid.getByRole('link', { name: 'All categories', exact: true }).click()
    await expect(toggle).toContainText('Category')
    await expect(page).not.toHaveURL(/exclude_categories|category=/)
    await expect(rows(page)).toHaveCount(21)
})

for (const width of [390, 1280]) {
    test(`version dropdowns summarize multiple choices and clear each group independently at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 844 })
        const cake = page.getByRole('group', { name: 'CakePHP version', exact: true })
        const php = page.getByRole('group', { name: 'PHP version', exact: true })
        await expect(cake.locator('summary')).toContainText('CakePHP version')
        await expect(php.locator('summary')).toContainText('PHP version')
        await expect(cake.getByRole('button', { name: 'CakePHP 4.4', exact: true })).toBeHidden()

        await cake.locator('summary').click()
        const optionsTop = await cake.locator('.version-options').evaluate(element => element.offsetTop)
        const panelHeight = await cake.locator('.version-dropdown-panel').evaluate(element => element.offsetHeight)
        await cake.getByRole('button', { name: 'CakePHP 4.4', exact: true }).click()
        await expect(rows(page)).toHaveCount(1)
        await expect(cake.locator('summary')).toContainText('4.4')
        expect(await cake.locator('.version-options').evaluate(element => element.offsetTop)).toBe(optionsTop)
        expect(await cake.locator('.version-dropdown-panel').evaluate(element => element.offsetHeight)).toBe(panelHeight)
        await expect(cake.locator('details')).toHaveAttribute('open', /^(open)?$/)
        await cake.getByRole('button', { name: 'CakePHP 5.4', exact: true }).click()
        await expect(cake.locator('summary')).toContainText('CakePHP (2)')
        await expect(rows(page)).toHaveCount(21)
        await expect(cake.getByRole('button', { name: 'CakePHP 4.4', exact: true })).toHaveAttribute('aria-pressed', 'true')

        await php.locator('summary').click()
        await expect(cake.locator('details')).not.toHaveAttribute('open')
        await php.getByRole('button', { name: 'PHP 8.5', exact: true }).click()
        await expect(php.locator('summary')).toContainText('8.5')
        await cake.locator('summary').click()
        await cake.getByRole('link', { name: 'Clear CakePHP version', exact: true }).click()
        await expect(cake.locator('summary')).toContainText('CakePHP version')
        expect(await cake.locator('.version-options').evaluate(element => element.offsetTop)).toBe(optionsTop)
        expect(await cake.locator('.version-dropdown-panel').evaluate(element => element.offsetHeight)).toBe(panelHeight)
        await expect(php.locator('summary')).toContainText('8.5')
        await expect(page).not.toHaveURL(/cakephp_slugs/)
        await expect(page).toHaveURL(/php_slugs/)
        await expect(cake.getByRole('button', { name: 'CakePHP 4.4', exact: true })).toHaveAttribute('aria-pressed', 'false')
        expect(await cake.locator('.version-dropdown-panel').evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true)
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true)
        await page.screenshot({ path: 'tmp/mobile-version-selectors.png', animations: 'disabled' })
        await page.keyboard.press('Escape')
        await expect(cake.locator('details')).not.toHaveAttribute('open')
        await expect(cake.locator('summary')).toBeFocused()
        await php.locator('summary').click()
        await search(page).click()
        await expect(php.locator('details')).not.toHaveAttribute('open')
        await retained(page)
    })
}

test('plugin details filter the parent directory while retaining search and sort', async ({ page }) => {
    await chooseSort(page, 'Downloads')
    await search(page).fill('users')
    await expect(page).toHaveURL(/search=users/)
    await rows(page).getByRole('link', { name: 'cakedc/users', exact: true }).click()
    const dialog = page.getByRole('dialog', { name: 'cakedc/users', exact: true })
    await expect(dialog).toBeVisible()
    await expect(dialog).toContainText('composer require cakedc/users')
    await dialog.getByRole('group', { name: 'CakePHP compatibility' }).getByRole('link', { name: 'CakePHP 4.4', exact: true }).click()
    await expect(dialog).toBeHidden()
    await expect(page).toHaveURL(/cakephp_slugs/)
    await expect(page).toHaveURL(/search=users/)
    await expect(page).toHaveURL(/sort=downloads/)
    await expect(rows(page)).toHaveCount(1)
    await retained(page)
})

test('one shared DaisyUI dialog loads details, copies installation and restores focus and scroll', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write'])
    await expect(page.locator('dialog.modal')).toHaveCount(1)
    await expect(page.locator('#plugin-details-content')).toBeEmpty()
    const trigger = rows(page).first().locator('.package-name a')
    const name = (await trigger.innerText()).trim()
    const url = page.url()
    await trigger.focus()
    const scroll = await page.evaluate(() => scrollY)
    await trigger.press('Enter')
    const dialog = page.getByRole('dialog', { name, exact: true })
    await expect(dialog).toBeVisible()
    await expect(dialog.getByRole('heading', { name: 'Install with Composer' })).toBeVisible()
    await expect(dialog.locator('.plugin-stats')).toContainText('Latest stable')
    await expect(dialog.locator('.plugin-stats')).toContainText('Released')
    await expect(dialog.locator('.plugin-stats')).toContainText('Stars')
    expect(await dialog.getByRole('link', { name: 'Repository' }).evaluate(el => ({
        text: getComputedStyle(el).color,
        background: getComputedStyle(el).backgroundColor,
    }))).toEqual({ text: 'rgb(255, 255, 255)', background: 'rgb(183, 44, 52)' })
    await dialog.getByRole('button', { name: 'Copy install command', exact: true }).click()
    await expect(dialog.getByRole('button', { name: 'Command copied', exact: true })).toBeVisible()
    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe(`composer require ${name}`)
    await expect(page).toHaveURL(url)
    await page.keyboard.press('Escape')
    await expect(dialog).toBeHidden()
    await expect(trigger).toBeFocused()
    expect(await page.evaluate(() => scrollY)).toBe(scroll)
    await retained(page)
})

test('community picks use the same dialog and backdrop dismissal works', async ({ page }) => {
    const trigger = featured(page).first().locator('.package-name a')
    const name = await trigger.getAttribute('data-plugin-name')
    await trigger.click()
    const dialog = page.getByRole('dialog', { name, exact: true })
    await expect(dialog).toContainText(`composer require ${name}`)
    await page.locator('.modal-backdrop button').click({ position: { x: 5, y: 5 } })
    await expect(dialog).toBeHidden()
    await expect(trigger).toBeFocused()
})

test('loading a different plugin hides stale details and a failed request can be retried', async ({ page }) => {
    await rows(page).first().locator('.package-name a').click()
    const dialog = page.locator('#plugin-modal')
    await expect(dialog.locator('.plugin-details')).toBeVisible()
    const previous = await dialog.locator('.plugin-install-command code').innerText()
    await dialog.getByRole('button', { name: 'Close plugin details', exact: true }).click()
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        await gate
        await route.fulfill({ status: 503, contentType: 'application/json', body: '{}' })
    }, { times: 1 })
    const trigger = rows(page).nth(1).locator('.package-name a')
    const name = (await trigger.innerText()).trim()
    try {
        await trigger.click()
        await expect(dialog.getByRole('status').filter({ hasText: 'Loading plugin details' })).toBeVisible()
        await expect(dialog.locator('#plugin-details-content')).toBeHidden()
    } finally {
        release()
    }
    await expect(dialog.getByRole('alert')).toContainText('We couldn’t load this plugin')
    await dialog.getByRole('button', { name: 'Retry', exact: true }).click()
    await expect(dialog.locator('.plugin-install-command code')).toHaveText(`composer require ${name}`)
    await expect(dialog.locator('.plugin-install-command code')).not.toHaveText(previous)
    await expect(dialog.getByRole('alert')).toBeHidden()
})

test('throttled plugin details offer a working normal page link', async ({ page }) => {
    await page.route('**/_interact/update', route => route.fulfill({
        status: 429,
        headers: { 'Retry-After': '60' },
        contentType: 'application/json',
        body: '{}',
    }), { times: 1 })
    const trigger = rows(page).first().locator('.package-name a')
    const href = await trigger.getAttribute('href')
    const name = (await trigger.innerText()).trim()
    await trigger.click()
    const dialog = page.locator('#plugin-modal')
    await expect(dialog.getByRole('alert')).toContainText('Too many requests')
    await expect(dialog.getByRole('button', { name: 'Retry', exact: true })).toBeHidden()
    await expect(dialog.getByRole('link', { name: 'Reload page', exact: true })).toBeHidden()
    await expect(dialog.getByRole('link', { name: 'Open plugin page', exact: true })).toHaveAttribute('href', new URL(href, page.url()).href)
    await dialog.getByRole('link', { name: 'Open plugin page', exact: true }).click()
    await expect(page).toHaveURL(new URL(href, page.url()).href)
    await expect(page.locator('.plugin-install-command code')).toHaveText(`composer require ${name}`)
})

test('modal fits mobile screens and contains keyboard focus', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 740 })
    await rows(page).first().locator('.package-name a').click()
    const dialog = page.locator('#plugin-modal')
    await expect(dialog.locator('.plugin-details')).toBeVisible()
    const box = await dialog.locator('.modal-box').boundingBox()
    expect(box.x).toBeGreaterThanOrEqual(0)
    expect(box.x + box.width).toBeLessThanOrEqual(320)
    expect(box.height).toBeLessThanOrEqual(740)
    expect(await dialog.locator('.modal-box').evaluate(el => el.scrollWidth > el.clientWidth)).toBe(false)
    for (let i = 0; i < 12; i++) {
        await page.keyboard.press('Tab')
        expect(await dialog.evaluate(el => el.contains(document.activeElement) || document.activeElement === document.body)).toBe(true)
    }
    await dialog.getByRole('button', { name: 'Close plugin details', exact: true }).click()
    await expect(dialog).toBeHidden()
})

test('no results explain how to recover', async ({ page }) => {
    await search(page).fill('no-such-plugin-name')
    await expect(page.getByRole('heading', { name: 'No plugins found' })).toBeVisible()
    await page.locator('.empty-results').getByRole('link', { name: 'Clear all' }).click()
    await expect(rows(page)).toHaveCount(21)
    await expect(search(page)).toHaveValue('')
})

test('search shows immediate inline feedback and leaves existing results readable', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/_interact/update', async route => {
        await gate
        await route.continue()
    })
    const request = page.waitForRequest('**/_interact/update')
    try {
        await search(page).fill('asset')
        await expect(page.getByRole('status', { name: 'Updating results', exact: true })).toBeVisible()
        await request
        await expect(search(page)).toHaveAttribute('aria-busy', 'true')
        await expect(rows(page)).toHaveCount(21)
        expect(await rows(page).first().evaluate(element => getComputedStyle(element).opacity)).toBe('1')
    } finally {
        release()
    }
    await expect(rows(page)).toHaveCount(1)
    await expect(page.getByRole('status', { name: 'Updating results', exact: true })).toBeHidden()
})

test('a failed update keeps results and can be retried', async ({ page }) => {
    await page.route('**/_interact/update', route => route.fulfill({ status: 503, contentType: 'application/json', body: '{}' }), { times: 1 })
    await search(page).fill('asset')
    await expect(page.getByRole('alert')).toContainText('We couldn’t update the results')
    await expect(rows(page)).toHaveCount(21)
    await page.getByRole('button', { name: 'Retry', exact: true }).click()
    await expect(rows(page)).toHaveCount(1)
    await expect(page.getByRole('alert')).toBeHidden()
})

test('navigation preserves the cookie theme and current page indicator', async ({ page, context }) => {
    await page.locator('#theme-toggle').click()
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    const cookie = (await context.cookies()).find(cookie => cookie.name === 'theme')
    expect(cookie).toMatchObject({ value: 'cakephp-dark', httpOnly: true, sameSite: 'Lax', path: '/' })
    await page.locator('.desktop-nav').getByRole('link', { name: 'Listing requirements' }).click()
    await expect(page).toHaveURL(/\/requirements$/)
    await expect(page).toHaveTitle('Listing Requirements | CakePHP Plugins')
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'http://127.0.0.1:8765/requirements')
    await expect(page.getByRole('heading', { name: 'Listing requirements', exact: true })).toBeVisible()
    await expect(page.locator('.desktop-nav').getByRole('link', { name: 'Listing requirements' })).toHaveAttribute('aria-current', 'page')
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    await page.getByRole('link', { name: 'Browse plugins', exact: true }).last().click()
    await expect(search(page)).toBeVisible()
    await expect(page).toHaveTitle('Discover CakePHP plugins | CakePHP Plugins')
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', 'http://127.0.0.1:8765/')
    await retained(page)
})

test('page navigation shows the global loading bar', async ({ page }) => {
    let release
    const gate = new Promise(resolve => { release = resolve })
    await page.route('**/requirements', async route => {
        await gate
        await route.continue()
    })
    const loading = page.getByRole('progressbar', { name: 'Loading page' })
    try {
        await page.locator('.desktop-nav').getByRole('link', { name: 'Listing requirements' }).click()
        await expect(loading).toBeVisible()
        expect(await loading.evaluate(element => element.getBoundingClientRect().top)).toBe(0)
    } finally {
        release()
    }
    await expect(page).toHaveURL(/\/requirements$/)
    await expect(loading).toBeHidden()
    await retained(page)
})

test('the saved palette renders before JavaScript and is readable in both themes', async ({ page, context, browser }) => {
    expect(await page.evaluate(() => getComputedStyle(document.body).backgroundColor)).toBe('rgb(248, 249, 251)')
    await page.locator('#theme-toggle').click()
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    const response = await page.reload()
    expect(await response.text()).toContain('data-theme="cakephp-dark"')
    await expect(rows(page).first()).toBeVisible()
    expect(await page.evaluate(() => ({
        background: getComputedStyle(document.body).backgroundColor,
        text: getComputedStyle(document.body).color,
        muted: getComputedStyle(document.querySelector('.package-meta dt')).color,
        link: getComputedStyle(document.querySelector('.desktop-nav a[aria-current]')).color,
    }))).toEqual({ background: 'rgb(19, 20, 22)', text: 'rgb(241, 242, 244)', muted: 'rgb(166, 170, 179)', link: 'rgb(255, 146, 152)' })
    await rows(page).first().locator('.package-name a').click()
    const dialog = page.locator('#plugin-modal')
    await expect(dialog.locator('.plugin-details')).toBeVisible()
    expect(await dialog.getByRole('link', { name: 'Repository' }).evaluate(el => ({
        text: getComputedStyle(el).color,
        background: getComputedStyle(el).backgroundColor,
    }))).toEqual({ text: 'rgb(19, 20, 22)', background: 'rgb(255, 146, 152)' })
    await page.keyboard.press('Escape')
    const noScript = await browser.newContext({ javaScriptEnabled: false, storageState: await context.storageState() })
    try {
        const document = await noScript.newPage()
        await document.goto('http://127.0.0.1:8765/requirements')
        await expect(document.locator('html')).toHaveAttribute('data-theme', 'cakephp-dark')
    } finally {
        await noScript.close()
    }
})

test('mobile navigation and compatibility work without horizontal page overflow', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 })
    await expect(page.locator('#theme-toggle')).toBeVisible()
    await page.getByLabel('Open menu', { exact: true }).click()
    await page.locator('.mobile-menu').getByRole('link', { name: 'Listing requirements' }).click()
    await expect(page).toHaveURL(/\/requirements$/)
    await page.getByRole('link', { name: 'Browse plugins', exact: true }).last().click()
    await expect(search(page)).toBeVisible()
    for (const width of [320, 390, 768, 1440]) {
        await page.setViewportSize({ width, height: 900 })
        expect(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)).toBe(false)
    }
})
