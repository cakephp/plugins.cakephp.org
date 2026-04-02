import Swiper from 'swiper'
import { Autoplay, Navigation } from 'swiper/modules'

const initializeSelects = () => {
    if (typeof window.SlimSelect !== 'function') {
        return
    }

    const selects = document.querySelectorAll('select')

    selects.forEach((element) => {
        const placeholder = element.getAttribute('data-placeholder')

        new window.SlimSelect({
            select: element,
            settings: {
                placeholderText: placeholder,
            },
        })
    })
}

const initializeFeaturedPackagesSlider = () => {
    const slider = document.querySelector('[data-featured-packages-slider]')

    if (!slider) {
        return
    }

    new Swiper(slider, {
        modules: [Autoplay, Navigation],
        loop: true,
        slidesPerView: 1,
        spaceBetween: 24,
        grabCursor: true,
        // autoplay: {
        //     delay: 4000,
        //     disableOnInteraction: false,
        //     pauseOnMouseEnter: true,
        // },
        navigation: {
            nextEl: '[data-featured-packages-next]',
            prevEl: '[data-featured-packages-prev]',
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
}

initializeSelects()
initializeFeaturedPackagesSlider()
