import Swiper from 'swiper'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import htmx from 'htmx.org'
import SlimSelect from 'slim-select'
import Alpine from 'alpinejs'

window.htmx = htmx

Alpine.data('packageSearch', () => ({
    query: '',
    results: [],
    open: false,
    loading: false,
    selectedIndex: -1,
    abortController: null,
    debounceTimer: null,

    init() {
        // Sync initial value from the input
        this.query = this.$refs.input?.value || ''

        this.$watch('query', (value) => {
            this.debouncedFetch(value)
        })
    },

    debouncedFetch(value) {
        clearTimeout(this.debounceTimer)

        if (this.abortController) {
            this.abortController.abort()
            this.abortController = null
        }

        if (value.trim().length < 2) {
            this.results = []
            this.open = false
            this.loading = false
            return
        }

        this.loading = true

        this.debounceTimer = setTimeout(() => {
            this.fetchResults(value.trim())
        }, 250)
    },

    async fetchResults(q) {
        if (this.abortController) {
            this.abortController.abort()
        }

        this.abortController = new AbortController()

        try {
            const response = await fetch(`/autocomplete?q=${encodeURIComponent(q)}`, {
                signal: this.abortController.signal,
                headers: { 'Accept': 'application/json' },
            })

            const data = await response.json()
            this.results = data
            this.open = data.length > 0
            this.selectedIndex = -1
        } catch (e) {
            if (e.name !== 'AbortError') {
                this.results = []
                this.open = false
            }
        } finally {
            this.loading = false
        }
    },

    close() {
        this.open = false
        this.selectedIndex = -1
    },

    onKeydown(e) {
        if (!this.open) return

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault()
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1)
                this.scrollToSelected()
                break
            case 'ArrowUp':
                e.preventDefault()
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1)
                this.scrollToSelected()
                break
            case 'Enter':
                if (this.selectedIndex >= 0) {
                    e.preventDefault()
                    this.selectResult(this.results[this.selectedIndex])
                }
                break
            case 'Escape':
                this.close()
                break
        }
    },

    scrollToSelected() {
        this.$nextTick(() => {
            const el = this.$refs.listbox?.querySelector('[aria-selected="true"]')
            el?.scrollIntoView({ block: 'nearest' })
        })
    },

    selectResult(result) {
        try {
            const url = new URL(result.repo_url)
            if (url.protocol === 'https:') {
                window.open(url.toString(), '_blank', 'noopener,noreferrer')
            }
        } catch {
            // Invalid URL, ignore
        }
        this.close()
    },



    formatNumber(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M'
        if (num >= 1000) return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k'
        return String(num)
    },
}))

const initializeSelects = (root = document) => {
    const selects = document.querySelectorAll('select')

    selects.forEach(select => {
        const initialized = select.getAttribute('data-slimselect-initialized');

        if (initialized === 'true') {
            return
        }

        select.setAttribute('data-slimselect-initialized', 'true')
        const placeholder = select.getAttribute('data-placeholder');

        new SlimSelect({
            select: select,
            settings: {
                placeholderText: placeholder,
            },
        })
    })
}

const initializeFeaturedPackagesSlider = (root = document) => {
    const slider = root.querySelector('[data-featured-packages-slider]')

    if (!slider) {
        return
    }

    if (slider.dataset.swiperInitialized === 'true') {
        return
    }

    const pagination = root.querySelector('[data-featured-packages-pagination]')

    new Swiper(slider, {
        modules: [Autoplay, Navigation, Pagination],
        loop: true,
        loopAdditionalSlides: 3,
        slidesPerView: 1,
        spaceBetween: 24,
        grabCursor: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        },
        navigation: {
            nextEl: '[data-featured-packages-next]',
            prevEl: '[data-featured-packages-prev]',
        },
        pagination: {
            el: pagination,
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1280: {
                slidesPerView: 3,
            },
        },
    })

    slider.dataset.swiperInitialized = 'true'
}

document.addEventListener('DOMContentLoaded', () => {
    initializeFeaturedPackagesSlider()
    initializeSelects()
})

Alpine.start()

if (typeof window.htmx !== 'undefined') {
    const reinitializeDynamicUi = () => {
        initializeFeaturedPackagesSlider(document)
        initializeSelects(document)
    }

    document.body.addEventListener('htmx:afterSettle', reinitializeDynamicUi)

    // Track loading state
    let isLoading = false
    document.body.addEventListener('htmx:beforeRequest', () => {
        isLoading = true
        document.body.classList.add('is-htmx-loading')
    })

    document.body.addEventListener('htmx:afterRequest', () => {
        isLoading = false
        document.body.classList.remove('is-htmx-loading')
    })
}
