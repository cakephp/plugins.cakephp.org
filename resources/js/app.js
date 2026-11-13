import 'vite/modulepreload-polyfill'

const register = (Alpine) => {
    Alpine.data('adminNavigation', () => ({ open: false }))

    Alpine.data('adminDialog', () => ({
        init() {
            // Keep the server-rendered open attribute across morphs and enter the native modal layer.
            this.$el.removeAttribute('open')
            this.$el.showModal()
        },
    }))
    Alpine.store('theme', {
        dark: document.documentElement.dataset.theme === 'cakephp-dark',
        apply(event) {
            this.dark = event.detail.theme === 'cakephp-dark'
            document.documentElement.dataset.theme = event.detail.theme
        },
    })

    Alpine.data('namespaceCounters', () => ({
        frame: null,
        init() {
            const motion = window.matchMedia('(prefers-reduced-motion: reduce)')
            if (motion.matches) return
            const counters = Array.from(this.$el.querySelectorAll('[data-count-up-value]'), (element, index) => ({
                element,
                total: Number(element.dataset.countUpValue),
                final: element.textContent.trim(),
                delay: index * 200,
            }))
            const formatter = new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 })
            const started = performance.now()
            const tick = now => {
                let complete = true
                for (const counter of counters) {
                    const progress = motion.matches ? 1 : Math.max(0, Math.min((now - started - counter.delay) / 1100, 1))
                    const eased = 1 - (1 - progress) ** 3
                    counter.element.textContent = progress === 1
                        ? counter.final
                        : formatter.format(Math.round(counter.total * eased)).replace('K', 'k')
                    complete &&= progress === 1
                }
                this.frame = complete ? null : requestAnimationFrame(tick)
            }
            this.frame = requestAnimationFrame(tick)
        },
        destroy() {
            if (this.frame !== null) cancelAnimationFrame(this.frame)
        },
    }))

    Alpine.data('catalogue', () => ({
        categorySearch: '',
        categoriesToggled(event) {
            if (event.target.open) this.categorySearch = ''
        },
        matchesCategory(name) {
            return name.toLocaleLowerCase().includes(this.categorySearch.trim().toLocaleLowerCase())
        },
        hasCategoryMatches() {
            return Array.from(this.$refs.categoryGrid.querySelectorAll('[data-category-name]'))
                .some(category => this.matchesCategory(category.dataset.categoryName))
        },
        closeCategories(focusToggle = false) {
            if (!this.$refs.categoryBrowser.open && !focusToggle) return
            this.$refs.categoryBrowser.open = false
            this.$refs.categoryBrowser.querySelector('summary').focus({ preventScroll: true })
            this.categorySearch = ''
        },
        activate(event) {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
            const sort = event.target.closest('.sort-option')
            if (sort) sort.closest('details')?.removeAttribute('open')
            const control = event.target.closest('[data-version-group][data-version-slug], [data-clear-catalogue], [data-category-slug], [data-toggle-awesome]')
            if (!control || !this.$el.contains(control)) return
            event.preventDefault()
            if (control.hasAttribute('data-clear-catalogue')) return this.$server.call('clearFilters')
            if (control.hasAttribute('data-category-slug')) {
                this.closeCategories(control.classList.contains('selected-category'))
                return this.$server.call('selectCategory', control.dataset.categorySlug)
            }
            if (control.hasAttribute('data-toggle-awesome')) return this.$server.call('toggleAwesome')
            const { versionGroup, versionSlug } = control.dataset
            return this.$server.call('toggleVersion', versionGroup, versionSlug)
        },
        selectVersion(event) {
            return this.$server.call('selectVersion', event.detail.group, event.detail.slug)
        },
        selectCategory(event) {
            this.closeCategories()
            return this.$server.call('selectCategory', event.detail.slug)
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
        activate(event) {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
            const plugin = event.target.closest('[data-open-plugin]')
            if (!plugin || plugin.hasAttribute('download') || (plugin.target && plugin.target !== '_self')) return
            event.preventDefault()
            plugin.dispatchEvent(new CustomEvent('plugin-open', {
                bubbles: true,
                detail: { id: Number(plugin.dataset.pluginId), name: plugin.dataset.pluginName },
            }))
        },
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
            if (this.$root.dataset.catalogueFilters === 'false') return
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
            const version = event.target.closest('[data-filter-package-version]')
            const category = event.target.closest('[data-filter-package-category]')
            if (!version && !category) return
            event.preventDefault()
            this.$refs.dialog.close()
            if (category) this.$dispatch('catalogue-category', { slug: category.dataset.categorySlug })
            else this.$dispatch('catalogue-version', { group: version.dataset.versionGroup, slug: version.dataset.versionSlug })
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
