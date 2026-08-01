import {
    Activity,
    ArrowRight,
    Baby,
    Badge,
    BadgeCheck,
    Bell,
    CalendarCheck,
    CalendarDays,
    Check,
    ChevronDown,
    ClipboardCheck,
    Clock3,
    createIcons,
    Download,
    ExternalLink,
    FileText,
    FlaskConical,
    HeartHandshake,
    HeartPulse,
    History,
    Hospital,
    Languages,
    Mail,
    MapPin,
    Megaphone,
    Menu,
    Microscope,
    Phone,
    Search,
    SearchX,
    ShieldCheck,
    ShieldPlus,
    Snowflake,
    Stethoscope,
    TestTubes,
    ThermometerSnowflake,
    Truck,
    UserCog,
    UserRoundCheck,
    Users,
    X,
} from 'lucide';

createIcons({
    icons: {
        Activity,
        ArrowRight,
        Baby,
        Badge,
        BadgeCheck,
        Bell,
        CalendarCheck,
        CalendarDays,
        Check,
        ChevronDown,
        ClipboardCheck,
        Clock3,
        Download,
        ExternalLink,
        FileText,
        FlaskConical,
        HeartHandshake,
        HeartPulse,
        History,
        Hospital,
        Languages,
        Mail,
        MapPin,
        Megaphone,
        Menu,
        Microscope,
        Phone,
        Search,
        SearchX,
        ShieldCheck,
        ShieldPlus,
        Snowflake,
        Stethoscope,
        TestTubes,
        ThermometerSnowflake,
        Truck,
        UserCog,
        UserRoundCheck,
        Users,
        X,
    },
    attrs: {
        'stroke-width': 1.8,
    },
});

const menuButton = document.querySelector('[data-menu-button]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

const setMenuState = (isOpen) => {
    if (!(menuButton instanceof HTMLButtonElement) || !(mobileMenu instanceof HTMLElement)) {
        return;
    }

    menuButton.setAttribute('aria-expanded', String(isOpen));
    menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
    mobileMenu.hidden = !isOpen;
    document.body.classList.toggle('menu-is-open', isOpen);

    menuButton.innerHTML = `<i data-lucide="${isOpen ? 'x' : 'menu'}" width="22" height="22"></i>`;
    createIcons({ icons: { Menu, X }, attrs: { 'stroke-width': 1.8 } });
};

menuButton?.addEventListener('click', () => {
    setMenuState(menuButton.getAttribute('aria-expanded') !== 'true');
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        setMenuState(false);
    }
});

const revealItems = document.querySelectorAll('.reveal-on-scroll');

if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

    revealItems.forEach((item) => observer.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
}
