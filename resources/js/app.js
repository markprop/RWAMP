import './bootstrap';
import Alpine from 'alpinejs';

// Countdown Timer Component
Alpine.data('countdown', () => ({
    targetDate: new Date('2024-06-01T00:00:00Z'),
    timeLeft: {
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0
    },
    completed: false,

    init() {
        this.updateCountdown();
        setInterval(() => this.updateCountdown(), 1000);
    },

    updateCountdown() {
        const now = new Date().getTime();
        const distance = this.targetDate.getTime() - now;

        if (distance < 0) {
            this.completed = true;
            return;
        }

        this.timeLeft = {
            days: Math.floor(distance / (1000 * 60 * 60 * 24)),
            hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((distance % (1000 * 60)) / 1000)
        };
    }
}));

// Smooth scrolling
Alpine.data('smoothScroll', () => ({
    scrollTo(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }
}));

// Form handling
Alpine.data('contactForm', () => ({
    formData: {
        name: '',
        email: '',
        phone: '',
        message: ''
    },
    loading: false,
    success: false,
    error: false,

    async submitForm() {
        this.loading = true;
        this.error = false;
        this.success = false;

        try {
            const response = await fetch('/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.formData)
            });

            if (response.ok) {
                this.success = true;
                this.formData = { name: '', email: '', phone: '', message: '' };
            } else {
                this.error = true;
            }
        } catch (error) {
            this.error = true;
        } finally {
            this.loading = false;
        }
    }
}));

// Reseller form handling
Alpine.data('resellerForm', () => ({
    formData: {
        name: '',
        email: '',
        phone: '',
        company: '',
        experience: '',
        message: '',
        investmentCapacity: ''
    },
    loading: false,
    success: false,
    error: false,

    async submitForm() {
        this.loading = true;
        this.error = false;
        this.success = false;

        try {
            const response = await fetch('/reseller', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.formData)
            });

            if (response.ok) {
                this.success = true;
                this.formData = { name: '', email: '', phone: '', company: '', experience: '', message: '', investmentCapacity: '' };
            } else {
                this.error = true;
            }
        } catch (error) {
            this.error = true;
        } finally {
            this.loading = false;
        }
    }
}));

// Newsletter signup
Alpine.data('newsletterForm', () => ({
    email: '',
    whatsapp: '',
    loading: false,
    success: false,
    error: false,

    async submitForm() {
        this.loading = true;
        this.error = false;
        this.success = false;

        try {
            const response = await fetch('/newsletter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: this.email, whatsapp: this.whatsapp })
            });

            if (response.ok) {
                this.success = true;
                this.email = '';
                this.whatsapp = '';
            } else {
                this.error = true;
            }
        } catch (error) {
            this.error = true;
        } finally {
            this.loading = false;
        }
    }
}));

// Global store for modal
Alpine.store('modal', {
    open: false,
});

// Presale modal logic: countdown + progress
Alpine.data('presaleModal', () => ({
    // Set your Phase 1 end date here
    targetDate: new Date('2025-12-31T23:59:59Z'),
    timeLeft: { days: 0, hours: 0, minutes: 0, seconds: 0 },
    completed: false,

    // Token economics for Phase 1
    totalTokensForPhase: 50_000_000,
    soldTokens: 30_000_000,
    priceUsd: 0.003,             // 1 RWAMP = 0.003 USDT
    privateSaleTargetUsd: 175_000,

    init() {
        this.updateCountdown();
        setInterval(() => this.updateCountdown(), 1000);
    },

    get progress() {
        const p = Math.min(100, Math.max(0, (this.soldTokens / this.totalTokensForPhase) * 100));
        return p.toFixed(1);
    },

    get raisedUsd() {
        return this.soldTokens * this.priceUsd;
    },

    updateCountdown() {
        const now = new Date().getTime();
        const distance = this.targetDate.getTime() - now;
        if (distance < 0) {
            this.completed = true;
            return;
        }
        this.timeLeft = {
            days: Math.floor(distance / (1000 * 60 * 60 * 24)),
            hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((distance % (1000 * 60)) / 1000)
        };
    }
}));

// Start Alpine
window.Alpine = Alpine;
Alpine.start();
