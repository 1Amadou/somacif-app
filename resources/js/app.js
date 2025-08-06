

import '../css/app.css';
import './bootstrap';

// Importation de Swiper
import Swiper from 'swiper';
import { Navigation, Pagination, EffectFade, Thumbs, Autoplay } from 'swiper/modules';

document.addEventListener('DOMContentLoaded', () => {
    // Slider de la page d'accueil
    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            modules: [EffectFade, Autoplay],
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: { delay: 7000, disableOnInteraction: false },
        });
    }

    // Galerie de la page détail produit
    if (document.querySelector('.product-gallery-main')) {
        const galleryThumbs = new Swiper('.product-gallery-thumbs', {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
        });
        new Swiper('.product-gallery-main', {
            modules: [Navigation, Thumbs],
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            thumbs: {
                swiper: galleryThumbs,
            },
        });
    }
});