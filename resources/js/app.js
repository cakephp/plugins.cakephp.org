import Swiper from 'swiper'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import htmx from 'htmx.org'
import SlimSelect from 'slim-select'

window.htmx = htmx

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
