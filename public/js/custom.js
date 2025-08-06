document.addEventListener('DOMContentLoaded', () => {
    // Logique pour le menu mobile (via Alpine.js, ce script est un fallback)
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Logique pour le header qui change au défilement
    const header = document.getElementById('header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });
    }

    // Logique pour les animations de section au défilement
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, {
        threshold: 0.1
    });
    document.querySelectorAll('.fade-in-section').forEach(section => {
        observer.observe(section);
    });

    // Initialisation du slider de la page d'accueil
    if (document.querySelector('.hero-swiper')) {
        new Swiper('.hero-swiper', {
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: { delay: 7000, disableOnInteraction: false },
        });
    }
    
    // Initialisation du slider de la page catalogue visiteur
    if (document.querySelector('.product-showcase-swiper')) {
        new Swiper('.product-showcase-swiper', {
            loop: true,
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            autoplay: { delay: 4000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination' },
        });
    }

    // Initialisation de la galerie de la page détail produit
    if (document.querySelector('.product-gallery-main')) {
        const galleryThumbs = new Swiper('.product-gallery-thumbs', {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
        });
        new Swiper('.product-gallery-main', {
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