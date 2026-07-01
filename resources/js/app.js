import './bootstrap';
import Alpine from 'alpinejs';

document.documentElement.classList.add('js-reveal');

window.Alpine = Alpine;
Alpine.start();

const reveal = () => {
    const items = document.querySelectorAll('.reveal');

    if (!items.length) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.16,
        rootMargin: '0px 0px -8% 0px',
    });

    items.forEach((item, index) => {
        item.style.transitionDelay = `${Math.min(index * 55, 280)}ms`;
        observer.observe(item);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', reveal, { once: true });
} else {
    reveal();
}
