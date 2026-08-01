import {
    Activity,
    ArrowRight,
    Baby,
    Badge,
    BadgeCheck,
    Bell,
    CalendarCheck,
    CalendarClock,
    CalendarDays,
    ChartNoAxesCombined,
    Check,
    ChevronRight,
    ChevronDown,
    CircleCheck,
    ClipboardCheck,
    Clock3,
    createElement,
    Download,
    Droplets,
    ExternalLink,
    FileText,
    FlaskConical,
    HeartHandshake,
    HeartPulse,
    History,
    Hospital,
    Languages,
    LayoutDashboard,
    Mail,
    MapPin,
    Megaphone,
    Menu,
    Microscope,
    Newspaper,
    Phone,
    PackageCheck,
    PackageOpen,
    QrCode,
    RefreshCw,
    ScanLine,
    Search,
    SearchX,
    ShieldCheck,
    Shield,
    ShieldPlus,
    Snowflake,
    Settings,
    Siren,
    Stethoscope,
    TestTubes,
    ThermometerSnowflake,
    Truck,
    UserCog,
    UserPlus,
    UserRoundCheck,
    Users,
    X,
} from 'lucide';

document.documentElement.classList.add('has-reveal');

const icons = {
    activity: Activity,
    'arrow-right': ArrowRight,
    baby: Baby,
    badge: Badge,
    'badge-check': BadgeCheck,
    bell: Bell,
    'calendar-check': CalendarCheck,
    'calendar-clock': CalendarClock,
    'calendar-days': CalendarDays,
    'chart-no-axes-combined': ChartNoAxesCombined,
    check: Check,
    'chevron-down': ChevronDown,
    'chevron-right': ChevronRight,
    'circle-check': CircleCheck,
    'clipboard-check': ClipboardCheck,
    'clock-3': Clock3,
    download: Download,
    droplets: Droplets,
    'external-link': ExternalLink,
    'file-text': FileText,
    'flask-conical': FlaskConical,
    'heart-handshake': HeartHandshake,
    'heart-pulse': HeartPulse,
    history: History,
    hospital: Hospital,
    languages: Languages,
    'layout-dashboard': LayoutDashboard,
    mail: Mail,
    'map-pin': MapPin,
    megaphone: Megaphone,
    menu: Menu,
    microscope: Microscope,
    newspaper: Newspaper,
    'package-check': PackageCheck,
    'package-open': PackageOpen,
    phone: Phone,
    'qr-code': QrCode,
    'refresh-cw': RefreshCw,
    'scan-line': ScanLine,
    search: Search,
    'search-x': SearchX,
    settings: Settings,
    shield: Shield,
    'shield-check': ShieldCheck,
    'shield-plus': ShieldPlus,
    siren: Siren,
    snowflake: Snowflake,
    stethoscope: Stethoscope,
    'test-tubes': TestTubes,
    'thermometer-snowflake': ThermometerSnowflake,
    truck: Truck,
    'user-cog': UserCog,
    'user-plus': UserPlus,
    'user-round-check': UserRoundCheck,
    users: Users,
    x: X,
};

const renderLucideIcons = (root = document) => {
    const placeholders = [];

    if (root instanceof Element && root.matches('i[data-lucide]')) {
        placeholders.push(root);
    }

    if (root instanceof Document || root instanceof Element || root instanceof DocumentFragment) {
        placeholders.push(...root.querySelectorAll('i[data-lucide]'));
    }

    placeholders.forEach((placeholder) => {
        const name = placeholder.dataset.lucide;
        const icon = icons[name];

        if (!icon) {
            return;
        }

        const attributes = Object.fromEntries(
            [...placeholder.attributes]
                .filter(({ name: attributeName }) => attributeName !== 'data-lucide')
                .map(({ name: attributeName, value }) => [attributeName, value]),
        );
        const existingClass = attributes.class ?? '';
        const svg = createElement(icon, {
            ...attributes,
            class: `lucide lucide-${name} ${existingClass}`.trim(),
            'aria-hidden': 'true',
            'stroke-width': 1.8,
        });

        placeholder.replaceWith(svg);
    });
};

renderLucideIcons();

const menuButton = document.querySelector('[data-menu-button]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

const setMenuState = (isOpen) => {
    if (!(menuButton instanceof HTMLButtonElement) || !(mobileMenu instanceof HTMLElement)) {
        return;
    }

    menuButton.setAttribute('aria-expanded', String(isOpen));
    menuButton.setAttribute(
        'aria-label',
        isOpen ? menuButton.dataset.closeLabel : menuButton.dataset.openLabel,
    );
    mobileMenu.hidden = !isOpen;
    document.body.classList.toggle('menu-is-open', isOpen);

    menuButton.innerHTML = `<i data-lucide="${isOpen ? 'x' : 'menu'}" width="22" height="22"></i>`;
    renderLucideIcons(menuButton);
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

document.addEventListener('livewire:navigated', () => renderLucideIcons());

const iconObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (node instanceof Element || node instanceof DocumentFragment) {
                renderLucideIcons(node);
            }
        });
    });
});

iconObserver.observe(document.body, { childList: true, subtree: true });

window.nbtsQrScanner = (livewire) => ({
    active: false,
    error: '',
    detector: null,
    stream: null,
    timer: null,

    async start() {
        this.error = '';

        if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
            this.error = this.$el.dataset.unavailable;
            return;
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            this.$refs.video.srcObject = this.stream;
            await this.$refs.video.play();
            this.detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            this.active = true;
            await this.scan();
        } catch {
            this.stop();
            this.error = this.$el.dataset.unavailable;
        }
    },

    async scan() {
        if (!this.active || !this.detector) {
            return;
        }

        try {
            const codes = await this.detector.detect(this.$refs.video);

            if (codes[0]?.rawValue) {
                await livewire.runSearch(codes[0].rawValue);
                this.stop();
                return;
            }
        } catch {
            this.error = this.$el.dataset.unavailable;
            this.stop();
            return;
        }

        this.timer = window.setTimeout(() => this.scan(), 280);
    },

    stop() {
        this.active = false;
        window.clearTimeout(this.timer);
        this.stream?.getTracks().forEach((track) => track.stop());
        this.stream = null;

        if (this.$refs.video) {
            this.$refs.video.srcObject = null;
        }
    },
});
