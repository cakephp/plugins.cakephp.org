import 'vite/modulepreload-polyfill'

const register = (Alpine) => {
    Alpine.store('theme', {
        dark: document.documentElement.dataset.theme === 'cakephp-dark',
        apply(event) {
            this.dark = event.detail.theme === 'cakephp-dark'
            document.documentElement.dataset.theme = event.detail.theme
        },
    })

    Alpine.data('catalogue', () => ({
        activate(event) {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
            const plugin = event.target.closest('[data-open-plugin]')
            if (plugin) {
                event.preventDefault()
                plugin.dispatchEvent(new CustomEvent('plugin-open', {
                    bubbles: true,
                    detail: { id: Number(plugin.dataset.pluginId), name: plugin.dataset.pluginName },
                }))
                return
            }
            const sort = event.target.closest('.sort-option')
            if (sort) sort.closest('details')?.removeAttribute('open')
            const control = event.target.closest('[data-version-group][data-version-slug], [data-clear-catalogue]')
            if (!control || !this.$el.contains(control)) return
            event.preventDefault()
            if (control.hasAttribute('data-clear-catalogue')) return this.$server.call('clearFilters')
            const { versionGroup, versionSlug } = control.dataset
            return this.$server.call('toggleVersion', versionGroup, versionSlug)
        },
        selectVersion(event) {
            return this.$server.call('selectVersion', event.detail.group, event.detail.slug)
        },
        closeMenu(event) {
            const menu = event.target.closest('details[open]')
            if (!menu) return
            menu.open = false
            menu.querySelector('summary')?.focus()
        },
    }))

    Alpine.data('pluginModal', () => ({
        trigger: null,
        scrollPosition: 0,
        title: '',
        open(event) {
            this.trigger = event.target
            this.scrollPosition = window.scrollY
            this.title = event.detail.name
            this.$refs.dialog.showModal()
            this.$refs.box.scrollTop = 0
        },
        closed() {
            if (!this.trigger?.isConnected) return
            this.trigger.focus({ preventScroll: true })
            window.scrollTo({ top: this.scrollPosition, behavior: 'instant' })
        },
        filter(event) {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
            const version = event.target.closest('[data-filter-package-version]')
            if (!version) return
            event.preventDefault()
            this.$refs.dialog.close()
            this.$dispatch('catalogue-version', { group: version.dataset.versionGroup, slug: version.dataset.versionSlug })
        },
    }))

    Alpine.data('pluginDetails', () => ({
        id: null,
        load(event) {
            this.id = event.detail.id
            return this.$server.call('show', this.id)
        },
        retry() { return this.$server.call('show', this.id) },
    }))

    Alpine.data('packageInstall', () => ({
        copied: false,
        failed: false,
        async copy() {
            try {
                await navigator.clipboard.writeText(this.$refs.command.textContent.trim())
                this.copied = true
                this.failed = false
            } catch {
                this.failed = true
            }
        },
    }))

    Alpine.data('siteNavigation', () => ({
        closeMenu(event) {
            const menu = event.target.closest('details[open]')
            if (!menu) return
            menu.open = false
            menu.querySelector('summary')?.focus()
        },
        followLink(event) {
            if (event.target.closest('a')) event.target.closest('details')?.removeAttribute('open')
        },
    }))

    Alpine.data('flashMessage', () => ({ open: true, close() { this.open = false } }))
}

if (window.Alpine) register(window.Alpine)
else document.addEventListener('alpine:init', () => register(window.Alpine), { once: true })

const refreshDocument = () => {
    const hasQuery = new URL(window.location.href).search !== ''
    let robots = document.head.querySelector('meta[name="robots"]')
    if (hasQuery && !robots) {
        robots = document.createElement('meta')
        robots.name = 'robots'
        document.head.append(robots)
    }
    if (hasQuery) robots.content = 'noindex,nofollow'
    else robots?.remove()
}

document.addEventListener('interact:navigated', refreshDocument)
document.addEventListener('interact:request-finished', refreshDocument)
