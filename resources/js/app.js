import '../css/style.css'
import Swiper from 'swiper'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'

const register = (Alpine) => {
    Alpine.store('theme', {
        dark: document.documentElement.dataset.theme === 'cakephp-dark',
        apply() {
            document.documentElement.dataset.theme = this.dark ? 'cakephp-dark' : 'cakephp'
        },
        toggle() {
            this.dark = !this.dark
            this.apply()
            try { localStorage.setItem('theme', this.dark ? 'cakephp-dark' : 'cakephp') } catch {}
        },
    })

    Alpine.data('catalogue', () => ({
        toggleVersion(event) {
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
            event.preventDefault()
            const { versionGroup, versionSlug } = event.currentTarget.dataset
            return this.$server.call('toggleVersion', versionGroup, versionSlug)
        },
    }))

    Alpine.data('featuredPackages', () => {
        let slider
        return {
            init() {
                slider = new Swiper(this.$el.querySelector('[data-featured-packages-slider]'), {
                    modules: [Autoplay, Navigation, Pagination],
                    loop: true,
                    loopAdditionalSlides: 3,
                    slidesPerView: 1,
                    spaceBetween: 24,
                    grabCursor: true,
                    autoplay: { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true },
                    navigation: {
                        nextEl: this.$el.querySelector('[data-featured-packages-next]'),
                        prevEl: this.$el.querySelector('[data-featured-packages-prev]'),
                    },
                    pagination: { el: this.$el.querySelector('[data-featured-packages-pagination]'), clickable: true },
                    breakpoints: { 768: { slidesPerView: 2 }, 1280: { slidesPerView: 3 } },
                })
            },
            destroy() {
                slider?.destroy(true, true)
                slider = undefined
            },
        }
    })

    Alpine.data('compatibilityCard', () => ({
        openDialog(event) {
            this.$el.querySelector(`#${CSS.escape(event.currentTarget.dataset.dialogId)}`)?.showModal()
        },
    }))

    Alpine.data('packageSearch', () => ({
        open: false,
        selectedIndex: -1,
        get activeId() {
            return this.open && this.selectedIndex >= 0 ? `suggestion-${this.selectedIndex}` : ''
        },
        close() {
            this.open = false
            this.selectedIndex = -1
        },
        onInput() {
            this.selectedIndex = -1
            if (this.$refs.input.value.trim().length < 2) this.close()
        },
        refreshSuggestions(event) {
            if (event.detail.islandId !== this.$el.closest('[data-interact-id]').dataset.interactId) return
            this.$nextTick(() => this.showSuggestions())
        },
        showSuggestions() {
            this.open = this.$refs.input.value.trim().length >= 2
                && this.$refs.listbox.querySelector('a') !== null
                && document.activeElement === this.$refs.input
            this.selectedIndex = -1
        },
        onKeydown(event) {
            if (!this.open) return
            const links = this.$refs.listbox.querySelectorAll('a')
            if (event.key === 'Escape') {
                event.preventDefault()
                this.close()
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault()
                const step = event.key === 'ArrowDown' ? 1 : -1
                this.selectedIndex = Math.max(-1, Math.min(this.selectedIndex + step, links.length - 1))
                links[this.selectedIndex]?.scrollIntoView({ block: 'nearest' })
            } else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                event.preventDefault()
                links[this.selectedIndex]?.click()
                this.close()
            }
        },
        submit() {
            this.close()
            const params = new URL(window.location.href).searchParams
            const filters = {}
            for (const key of ['cakephp_slugs', 'php_slugs']) {
                filters[key] = []
                for (const [name, value] of params) {
                    if (name === key || name === `${key}[]` || new RegExp(`^${key}\\[\\d+\\]$`).test(name)) filters[key].push(value)
                }
            }
            return this.$server.call('submit', filters)
        },
    }))
}

if (window.Alpine) register(window.Alpine)
else document.addEventListener('alpine:init', () => register(window.Alpine), { once: true })

const refreshDocument = () => {
    window.Alpine?.store('theme')?.apply()
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
