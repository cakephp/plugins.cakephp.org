import { test, expect } from '@playwright/test'
import { execFileSync } from 'node:child_process'

const fixtures = mode => execFileSync('php', ['tests/browser/development-state.php', mode], { encoding: 'utf8' })
const rows = page => page.locator('[data-package-row]')
test.beforeEach(() => fixtures('seed'))
test.afterEach(() => fixtures('reset'))

test('development compatibility is explicit, reloadable and retained on publisher navigation', async ({ page }) => {
    await page.goto('/')
    await expect(rows(page)).toHaveCount(21)
    await expect(rows(page).filter({ hasText: 'future/preview' })).toHaveCount(0)
    await expect(page.getByRole('region', { name: 'Ecosystem snapshot', exact: true })).toContainText('26')
    await page.locator('#versions-cakephp_slugs > summary').click()
    const version = page.getByRole('button', { name: 'CakePHP 6.x@dev', exact: true })
    await expect(version).toHaveAttribute('title', 'CakePHP 6 development builds')
    await version.click()
    await expect(page).toHaveURL(/cakephp-6-x-dev/)
    await expect(rows(page)).toHaveCount(2)
    await page.reload()
    await expect(rows(page)).toHaveCount(2)
    await page.getByRole('link', { name: 'More by future', exact: true }).click()
    await expect(page).toHaveURL(/\/plugins\/future\?.*cakephp-6-x-dev/)
    await expect(rows(page)).toHaveCount(1)
    await expect(rows(page)).toContainText('future/preview')
    await page.goto('/publishers')
    await expect(page.locator('[data-publisher-namespace="future"]')).toHaveCount(0)
    await page.getByLabel('CakePHP version', { exact: true }).selectOption('6.x@dev')
    await expect(page.locator('[data-publisher-namespace]')).toHaveCount(2)
    await page.getByRole('heading', { name: 'future', exact: true }).getByRole('link').click()
    await expect(page).toHaveURL(/cakephp-6-x-dev/)
    await expect(rows(page)).toContainText('future/preview')
})

test('development details retain compatibility and one install command without a separate support section', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write'])
    await page.goto('/?cakephp_slugs[]=cakephp-6-x-dev')
    await expect(rows(page)).toHaveCount(2)
    await rows(page).filter({ hasText: 'future/preview' }).locator('.package-name a').click()
    const dialog = page.getByRole('dialog', { name: 'future/preview', exact: true })
    await expect(dialog.getByRole('region', { name: 'Development support', exact: true })).toHaveCount(0)
    const development = dialog.getByRole('region', { name: 'Installation', exact: true })
    await expect(dialog.getByRole('region', { name: 'Compatibility', exact: true })).toContainText('6.x@dev')
    const command = "composer require 'future/preview:dev-next' 'cakephp/cakephp:6.x-dev'"
    await expect(development.locator('code')).toHaveText(command)
    await development.getByRole('button', { name: 'Copy install command', exact: true }).click()
    await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe(command)
    for (const width of [390, 1280]) {
        await page.setViewportSize({ width, height: 900 })
        for (const theme of ['cakephp', 'cakephp-dark']) {
            await page.evaluate(theme => { document.documentElement.dataset.theme = theme }, theme)
            await development.scrollIntoViewIfNeeded()
            expect(await dialog.evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true)
            await page.screenshot({ path: `tmp/development-${theme}-${width}.png`, animations: 'disabled' })
        }
    }
    await page.goto('/plugins/future/preview')
    await expect(page.getByRole('region', { name: 'Development support', exact: true })).toHaveCount(0)
    await expect(page.getByRole('region', { name: 'Installation', exact: true })).toContainText(command)
    await expect(page.getByRole('link', { name: 'CakePHP 6.x@dev', exact: true })).toHaveAttribute('href', /cakephp-6-x-dev/)
})
