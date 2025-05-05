/**
 * Haber Sitesi - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuButton = document.querySelector('.menu-button');
    const mobileMenu = document.getElementById('mobileMenu');
    let isMenuOpen = false;

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            isMenuOpen = !isMenuOpen;
            
            if (isMenuOpen) {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('block');
                document.body.classList.add('overflow-hidden');
            } else {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('block');
                document.body.classList.remove('overflow-hidden');
            }
        });
    }
    
    // Handle sticky header
    const header = document.querySelector('header');
    const headerHeight = header ? header.offsetHeight : 0;
    
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > headerHeight) {
                header.classList.add('fixed', 'top-0', 'left-0', 'right-0', 'shadow-md', 'z-50');
                document.body.style.paddingTop = headerHeight + 'px';
            } else {
                header.classList.remove('fixed', 'top-0', 'left-0', 'right-0', 'shadow-md', 'z-50');
                document.body.style.paddingTop = '0';
            }
        });
    }
    
    // Initialize lazy loading for images
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for browsers without IntersectionObserver support
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        });
    }
    
    // Initialize search functionality
    const searchButton = document.querySelector('.search-button');
    const searchInput = document.querySelector('.search-input');
    
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (searchInput.value.trim()) {
                window.location.href = '/search?q=' + encodeURIComponent(searchInput.value.trim());
            }
        });
        
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && searchInput.value.trim()) {
                window.location.href = '/search?q=' + encodeURIComponent(searchInput.value.trim());
            }
        });
    }
    
    // Initialize sliders if any
    if (typeof Swiper !== 'undefined') {
        const sliders = document.querySelectorAll('.news-slider');
        
        sliders.forEach(slider => {
            new Swiper(slider, {
                slidesPerView: 1,
                spaceBetween: 20,
                pagination: {
                    el: slider.querySelector('.swiper-pagination'),
                    clickable: true
                },
                navigation: {
                    nextEl: slider.querySelector('.swiper-button-next'),
                    prevEl: slider.querySelector('.swiper-button-prev')
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2
                    },
                    1024: {
                        slidesPerView: 3
                    }
                }
            });
        });
    }
}); 