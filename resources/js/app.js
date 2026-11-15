import Alpine from 'alpinejs';

/**
 * Header: mobile menu + scroll-spy highlighting the section currently in view.
 */
Alpine.data('siteHeader', (sections = []) => ({
    open: false,
    scrolled: false,
    active: sections.length ? 'accueil' : null,

    init() {
        const onScroll = () => {
            this.scrolled = window.scrollY > 8;
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        if (!sections.length || !('IntersectionObserver' in window)) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.active = entry.target.id;
                    }
                });
            },
            { rootMargin: '-45% 0px -50% 0px' },
        );

        sections.forEach((id) => {
            const element = document.getElementById(id);
            if (element) {
                observer.observe(element);
            }
        });
    },

    isActive(id) {
        return this.active === id;
    },

    close() {
        this.open = false;
    },
}));

/**
 * Contact form: client-side validation (mirrors the server rules) + loading state.
 */
Alpine.data('contactForm', (initialErrors = {}) => ({
    submitting: false,
    errors: { ...initialErrors },

    validateField(field) {
        const name = field.name;
        if (!name || field.type === 'hidden' || field.name === 'website') {
            return true;
        }

        let message = '';
        if (field.validity.valueMissing) {
            message = field.type === 'checkbox' ? 'Vous devez accepter la politique de confidentialité.' : 'Ce champ est obligatoire.';
        } else if (field.validity.typeMismatch && field.type === 'email') {
            message = 'Veuillez saisir une adresse e-mail valide.';
        } else if (field.validity.patternMismatch && name === 'phone') {
            message = 'Veuillez saisir un numéro de téléphone valide.';
        } else if (field.validity.tooShort) {
            message = `Ce champ doit contenir au moins ${field.minLength} caractères.`;
        } else if (field.validity.tooLong) {
            message = `Ce champ ne peut pas dépasser ${field.maxLength} caractères.`;
        }

        if (message) {
            this.errors[name] = message;
        } else {
            delete this.errors[name];
        }

        field.setAttribute('aria-invalid', message ? 'true' : 'false');

        return !message;
    },

    submit(event) {
        const form = event.target;
        let firstInvalid = null;

        Array.from(form.elements).forEach((field) => {
            if (!this.validateField(field) && !firstInvalid) {
                firstInvalid = field;
            }
        });

        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
            return;
        }

        this.submitting = true;
    },
}));

window.Alpine = Alpine;
Alpine.start();
