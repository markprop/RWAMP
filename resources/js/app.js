import './bootstrap';
import Alpine from '@alpinejs/csp';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// -------------------------------------------------------------------------
// Per-tab session ID (for independent tab authentication)
// -------------------------------------------------------------------------
(function () {
    try {
        const storageKey = 'tabSessionId';
        let tabSessionId = window.sessionStorage.getItem(storageKey);

        if (!tabSessionId) {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                tabSessionId = window.crypto.randomUUID();
            } else {
                // Fallback UUID-ish string
                tabSessionId =
                    'tab-' +
                    Math.random().toString(36).slice(2) +
                    Date.now().toString(36);
            }
            window.sessionStorage.setItem(storageKey, tabSessionId);
        }

        // Set lightweight cookie scoped to this browser instance / tab
        let cookie = `tab_session_id=${encodeURIComponent(tabSessionId)}; path=/; SameSite=Lax`;
        if (window.location.protocol === 'https:') {
            cookie += '; Secure';
        }
        document.cookie = cookie;
    } catch (e) {
        // Fail silently – app should continue to work without per-tab sessions
        console.error('tabSessionId initialization failed', e);
    }
})();

// Initialize Laravel Echo for real-time messaging
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || process.env.MIX_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || process.env.MIX_PUSHER_APP_CLUSTER || 'ap2',
    forceTLS: true,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    }
});

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

// Profit Calculator Component
Alpine.data('profitCalculator', function() {
    return {
        buyPrice: '',
        sellPrice: '',
        profitPerToken: '0.00',
        showInfo: false,
        init() {
            // Watch for changes in buyPrice and sellPrice
            this.$watch('buyPrice', () => this.calculateProfit());
            this.$watch('sellPrice', () => this.calculateProfit());
            // Initial calculation
            this.calculateProfit();
        },
        calculateProfit() {
            const buy = parseFloat(this.buyPrice) || 0;
            const sell = parseFloat(this.sellPrice) || 0;
            const profit = Math.max(0, sell - buy);
            this.profitPerToken = profit.toFixed(2);
        }
    };
});

// Reseller form handling
Alpine.data('resellerForm', function() {
    return {
        formData: {
            name: '',
            email: '',
            phone: '',
            company: '',
            investmentCapacity: '',
            message: '',
            hp: ''
        },
        loading: false,
        success: false,
        error: false,
        errorMessage: '',
        tooltip: null,
        nameStatus: null,
        nameMessage: '',
        nameValidated: false,
        emailStatus: null,
        emailMessage: '',
        emailValidated: false,
        phoneStatus: null,
        phoneMessage: '',
        phoneValidated: false,
        validationTimeout: { email: null, phone: null },

        init() {
            // Initialize tooltip to null
            this.tooltip = null;
            // Initialize validation states
            this.nameStatus = null;
            this.emailStatus = null;
            this.phoneStatus = null;
        },
    showTooltip(tooltipName, event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        // Toggle tooltip
        if (this.tooltip === tooltipName) {
            this.tooltip = null;
        } else {
            this.tooltip = tooltipName;
        }
    },

    formatPhone(input) {
        let value = input.value.replace(/\D/g, ''); // Remove all non-digits
        
        // Auto-format to Pakistan format if it looks like a Pakistan number
        if (value.length > 0) {
            // If starts with 0, remove it and add +92
            if (value.startsWith('0')) {
                value = '92' + value.substring(1);
            }
            // If starts with 92 and doesn't have +, add +
            if (value.startsWith('92') && !input.value.startsWith('+')) {
                value = '+' + value;
            }
            // If doesn't start with +, assume Pakistan and add +92
            if (!value.startsWith('+') && value.length >= 10) {
                // Check if it's a valid Pakistan mobile number (10 digits starting with 3)
                if (value.length === 10 && value.startsWith('3')) {
                    value = '+92' + value;
                } else if (value.length === 12 && value.startsWith('92')) {
                    value = '+' + value;
                }
            }
            
            // Format: +92 XXX XXXXXXX
            if (value.startsWith('+92') && value.length > 3) {
                const number = value.substring(3);
                if (number.length <= 3) {
                    input.value = '+92 ' + number;
                } else if (number.length <= 10) {
                    input.value = '+92 ' + number.substring(0, 3) + ' ' + number.substring(3);
                } else {
                    input.value = value;
                }
            } else {
                input.value = value;
            }
            
            this.formData.phone = input.value;
        }
    },

    validateName(name) {
        if (!name || name.trim() === '') {
            this.nameStatus = null;
            this.nameMessage = '';
            this.nameValidated = false;
            return;
        }

        const trimmedName = name.trim();
        
        if (trimmedName.length < 2) {
            this.nameStatus = 'invalid';
            this.nameMessage = 'Name must be at least 2 characters long';
            this.nameValidated = true;
            return;
        }

        if (!trimmedName.includes(' ')) {
            this.nameStatus = 'invalid';
            this.nameMessage = 'Please enter your full name (First Name and Last Name)';
            this.nameValidated = true;
            return;
        }

        if (!/^[a-zA-Z\s\-\'\.]+$/.test(trimmedName)) {
            this.nameStatus = 'invalid';
            this.nameMessage = 'Name can only contain letters, spaces, hyphens, apostrophes, and dots';
            this.nameValidated = true;
            return;
        }

        const nameParts = trimmedName.split(/\s+/).filter(part => part.length > 0);
        if (nameParts.length < 2) {
            this.nameStatus = 'invalid';
            this.nameMessage = 'Please enter both first name and last name';
            this.nameValidated = true;
            return;
        }

        if (nameParts.some(part => part.length < 2)) {
            this.nameStatus = 'invalid';
            this.nameMessage = 'Each name part must be at least 2 characters';
            this.nameValidated = true;
            return;
        }

        this.nameStatus = 'valid';
        this.nameMessage = 'Name format is valid';
        this.nameValidated = true;
    },

    async validateEmail(email) {
        if (this.validationTimeout.email) {
            clearTimeout(this.validationTimeout.email);
        }

        if (!email || email.trim() === '') {
            this.emailStatus = null;
            this.emailMessage = '';
            this.emailValidated = false;
            return;
        }

        if (email.trim().length < 5) {
            this.emailStatus = null;
            this.emailMessage = '';
            this.emailValidated = false;
            return;
        }

        // Only show checking status if email looks valid
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.trim())) {
            this.emailStatus = null;
            this.emailMessage = '';
            this.emailValidated = false;
            return;
        }

        this.validationTimeout.email = setTimeout(async () => {
            this.emailStatus = 'checking';
            this.emailMessage = 'Checking email...';
            this.emailValidated = true;

            try {
                const response = await fetch(`/api/check-email?email=${encodeURIComponent(email)}`);
                const data = await response.json();
                
                if (data.valid && !data.exists) {
                    this.emailStatus = 'valid';
                    this.emailMessage = data.message || 'Email is valid and available';
                } else if (data.exists) {
                    this.emailStatus = 'invalid';
                    this.emailMessage = data.message || 'This email is already registered';
                } else {
                    this.emailStatus = 'invalid';
                    this.emailMessage = data.message || 'Please enter a valid email address';
                }
            } catch (error) {
                this.emailStatus = null;
                this.emailMessage = '';
                this.emailValidated = false;
            }
        }, 500);
    },

    async validatePhone(phone) {
        if (this.validationTimeout.phone) {
            clearTimeout(this.validationTimeout.phone);
        }

        if (!phone || phone.trim() === '') {
            this.phoneStatus = null;
            this.phoneMessage = '';
            this.phoneValidated = false;
            return;
        }

        // Normalize phone for validation
        const normalized = phone.replace(/\s+/g, '').replace(/\D/g, '');
        if (normalized.length < 10) {
            this.phoneStatus = null;
            this.phoneMessage = '';
            this.phoneValidated = false;
            return;
        }

        this.validationTimeout.phone = setTimeout(async () => {
            this.phoneStatus = 'checking';
            this.phoneMessage = 'Checking phone number...';
            this.phoneValidated = true;

            try {
                const response = await fetch(`/api/check-phone?phone=${encodeURIComponent(phone)}`);
                const data = await response.json();
                
                if (data.valid && !data.exists) {
                    this.phoneStatus = 'valid';
                    this.phoneMessage = data.message || 'Phone number is valid and available';
                } else if (data.exists) {
                    this.phoneStatus = 'invalid';
                    this.phoneMessage = data.message || 'This phone number is already registered';
                } else {
                    this.phoneStatus = 'invalid';
                    this.phoneMessage = data.message || 'Please enter a valid phone number';
                }
            } catch (error) {
                this.phoneStatus = null;
                this.phoneMessage = '';
                this.phoneValidated = false;
            }
        }, 500);
    },

    getErrorMessage() {
        return this.errorMessage || 'Something went wrong. Please try again later.';
    },

    async submitForm() {
        this.loading = true;
        this.error = false;
        this.success = false;
        this.errorMessage = '';

        try {
            // Validate required fields
            if (!this.formData.name || !this.formData.email || !this.formData.phone || !this.formData.investmentCapacity) {
                this.error = true;
                this.errorMessage = 'Please fill in all required fields.';
                this.loading = false;
                return;
            }

            // Validate name format
            if (!this.nameValidated || this.nameStatus !== 'valid') {
                this.error = true;
                this.errorMessage = 'Please enter a valid full name (First Name and Last Name).';
                this.loading = false;
                return;
            }

            // Validate email
            if (!this.emailValidated || this.emailStatus !== 'valid') {
                this.error = true;
                this.errorMessage = 'Please enter a valid and available email address.';
                this.loading = false;
                return;
            }

            // Validate phone
            if (!this.phoneValidated || this.phoneStatus !== 'valid') {
                this.error = true;
                this.errorMessage = 'Please enter a valid phone number.';
                this.loading = false;
                return;
            }

            // reCAPTCHA v3 if available
            let recaptchaToken = '';
            if (window.grecaptcha) {
                recaptchaToken = await new Promise((resolve) => {
                    grecaptcha.ready(() => {
                        // try to infer site key from loaded script
                        const s = document.querySelector('script[src*="recaptcha"]');
                        const siteKey = s ? (new URL(s.src)).searchParams.get('render') : '';
                        if (!siteKey) return resolve('');
                        grecaptcha.execute(siteKey, { action: 'reseller' }).then(resolve);
                    });
                });
            }
            // Basic honeypot check on client side (server also validates)
            if (this.formData.hp) {
                this.error = true;
                this.errorMessage = 'Invalid submission.';
                this.loading = false;
                return; 
            }

            const response = await fetch('/reseller', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: this.formData.name,
                    email: this.formData.email,
                    phone: this.formData.phone,
                    company: this.formData.company || '',
                    investmentCapacity: this.formData.investmentCapacity,
                    message: this.formData.message || '',
                    hp: this.formData.hp || '',
                    recaptcha_token: recaptchaToken
                })
            });

            let data = null;
            try { 
                const text = await response.text();
                if (text) {
                    data = JSON.parse(text);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                // Try to get response text for debugging
                try {
                    const errorText = await response.clone().text();
                    console.error('Response text:', errorText);
                } catch (textError) {
                    console.error('Could not read response text:', textError);
                }
            }

            if (response.ok && data && data.success) {
                this.success = true;
                this.error = false;
                this.formData = { 
                    name: '', 
                    email: '', 
                    phone: '', 
                    company: '', 
                    investmentCapacity: '', 
                    message: '', 
                    hp: '' 
                };
                // Reset validation states
                this.nameValidated = false;
                this.emailValidated = false;
                this.phoneValidated = false;
                this.nameStatus = null;
                this.emailStatus = null;
                this.phoneStatus = null;
                // Scroll to success message
                setTimeout(() => {
                    const successEl = document.querySelector('[x-show="success"]');
                    if (successEl) successEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                this.error = true;
                // Show detailed error message
                if (data?.errors) {
                    // Laravel validation errors
                    const firstError = Object.values(data.errors)[0];
                    this.errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                } else if (data?.message) {
                    this.errorMessage = data.message;
                } else {
                    this.errorMessage = `Server error (${response.status}). Please try again later.`;
                }
                console.error('Form submission failed:', data, 'Status:', response.status);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.error = true;
            this.errorMessage = 'Network error. Please check your connection and try again.';
        } finally {
            this.loading = false;
        }
    }
    };
});

// Newsletter signup
Alpine.data('newsletterForm', () => ({
    email: '',
    whatsapp: '',
    hp: '',
    loading: false,
    success: false,
    error: false,

    async submitForm() {
        this.loading = true;
        this.error = false;
        this.success = false;

        try {
            if (this.hp) { this.error = true; this.loading = false; return; }
            const response = await fetch('/newsletter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: this.email, whatsapp: this.whatsapp, hp: this.hp })
            });

            let data = null;
            try { data = await response.json(); } catch (_) {}
            if (response.ok && data && data.success) {
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

// Signup tabs component
Alpine.data('signupTabs', () => ({
    tab: 'investor',
    tooltip: null,
    emailStatus: { investor: null, reseller: null },
    emailMessage: { investor: '', reseller: '' },
    emailValidated: { investor: false, reseller: false },
    phoneStatus: { investor: null, reseller: null },
    phoneMessage: { investor: '', reseller: '' },
    phoneValidated: { investor: false, reseller: false },
    passwordCriteria: { 
        investor: { 
            hasUpperCase: false, 
            hasLowerCase: false, 
            hasNumber: false, 
            hasSpecialChar: false, 
            minLength: false,
            hasValue: false
        },
        reseller: { 
            hasUpperCase: false, 
            hasLowerCase: false, 
            hasNumber: false, 
            hasSpecialChar: false, 
            minLength: false,
            hasValue: false
        }
    },
    nameStatus: { investor: null, reseller: null },
    nameMessage: { investor: '', reseller: '' },
    nameValidated: { investor: false, reseller: false },
    validationTimeout: { email: { investor: null, reseller: null }, phone: { investor: null, reseller: null } },
    init() {
        // Ensure tooltip is null on init
        this.tooltip = null;
        // Reset validation states
        this.emailStatus = { investor: null, reseller: null };
        this.emailMessage = { investor: '', reseller: '' };
        this.emailValidated = { investor: false, reseller: false };
        this.phoneStatus = { investor: null, reseller: null };
        this.phoneMessage = { investor: '', reseller: '' };
        this.phoneValidated = { investor: false, reseller: false };
        this.passwordCriteria = { 
            investor: { 
                hasUpperCase: false, 
                hasLowerCase: false, 
                hasNumber: false, 
                hasSpecialChar: false, 
                minLength: false,
                hasValue: false
            },
            reseller: { 
                hasUpperCase: false, 
                hasLowerCase: false, 
                hasNumber: false, 
                hasSpecialChar: false, 
                minLength: false,
                hasValue: false
            }
        };
        this.nameStatus = { investor: null, reseller: null };
        this.nameMessage = { investor: '', reseller: '' };
        this.nameValidated = { investor: false, reseller: false };
        
        // Auto-validate if referral code is pre-filled from URL
        this.$nextTick(() => {
            const referralInput = document.getElementById('referralCode');
            if (referralInput && referralInput.value) {
                this.validateReferralCode(referralInput.value);
            }
        });
    },
    tabClass(name) {
        return this.tab === name 
            ? 'border-primary text-primary bg-primary/5' 
            : 'border-gray-200 text-gray-700 hover:border-gray-300';
    },
    showTooltip(tooltipName, event) {
        // Prevent event propagation
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        // Close other tooltips and toggle current one
        if (this.tooltip === tooltipName) {
            this.tooltip = null;
        } else {
            this.tooltip = tooltipName;
        }
    },
    async validateEmail(email, type) {
        // Clear previous timeout
        if (this.validationTimeout.email[type]) {
            clearTimeout(this.validationTimeout.email[type]);
        }

        // Reset status if empty
        if (!email || email.trim() === '') {
            this.emailStatus[type] = null;
            this.emailMessage[type] = '';
            this.emailValidated[type] = false;
            return;
        }

        // Only validate if email length is reasonable (at least 5 chars for basic format)
        if (email.trim().length < 5) {
            this.emailStatus[type] = null;
            this.emailMessage[type] = '';
            this.emailValidated[type] = false;
            return;
        }

        // Debounce validation - wait 500ms after user stops typing
        this.validationTimeout.email[type] = setTimeout(async () => {
            this.emailStatus[type] = 'checking';
            this.emailMessage[type] = 'Checking email...';
            this.emailValidated[type] = true;

            try {
                const response = await fetch(`/api/check-email?email=${encodeURIComponent(email)}`);
                const data = await response.json();
                
                if (data.valid && !data.exists) {
                    this.emailStatus[type] = 'valid';
                    this.emailMessage[type] = data.message || 'Email is valid and available';
                } else if (data.exists) {
                    this.emailStatus[type] = 'invalid';
                    this.emailMessage[type] = data.message || 'This email is already registered';
                } else {
                    this.emailStatus[type] = 'invalid';
                    this.emailMessage[type] = data.message || 'Please enter a valid email address';
                }
            } catch (error) {
                this.emailStatus[type] = null;
                this.emailMessage[type] = '';
                this.emailValidated[type] = false;
            }
        }, 500);
    },
    async validatePhone(phone, type) {
        // Clear previous timeout
        if (this.validationTimeout.phone[type]) {
            clearTimeout(this.validationTimeout.phone[type]);
        }

        // Reset status if empty
        if (!phone || phone.trim() === '') {
            this.phoneStatus[type] = null;
            this.phoneMessage[type] = '';
            this.phoneValidated[type] = false;
            return;
        }

        // Only validate if phone starts with + and has reasonable length
        if (!phone.trim().startsWith('+') || phone.trim().length < 8) {
            this.phoneStatus[type] = null;
            this.phoneMessage[type] = '';
            this.phoneValidated[type] = false;
            return;
        }

        // Debounce validation - wait 500ms after user stops typing
        this.validationTimeout.phone[type] = setTimeout(async () => {
            this.phoneStatus[type] = 'checking';
            this.phoneMessage[type] = 'Checking phone number...';
            this.phoneValidated[type] = true;

            try {
                const response = await fetch(`/api/check-phone?phone=${encodeURIComponent(phone)}`);
                const data = await response.json();
                
                if (data.valid && !data.exists) {
                    this.phoneStatus[type] = 'valid';
                    this.phoneMessage[type] = data.message || 'Phone number is valid and available';
                } else if (data.exists) {
                    this.phoneStatus[type] = 'invalid';
                    this.phoneMessage[type] = data.message || 'This phone number is already registered';
                } else {
                    this.phoneStatus[type] = 'invalid';
                    this.phoneMessage[type] = data.message || 'Please enter a valid phone number';
                }
            } catch (error) {
                this.phoneStatus[type] = null;
                this.phoneMessage[type] = '';
                this.phoneValidated[type] = false;
            }
        }, 500);
    },
    validateName(name, type) {
        if (!name || name.trim() === '') {
            this.nameStatus[type] = null;
            this.nameMessage[type] = '';
            this.nameValidated[type] = false;
            return;
        }

        const trimmedName = name.trim();
        
        // Check if name has at least 2 characters
        if (trimmedName.length < 2) {
            this.nameStatus[type] = 'invalid';
            this.nameMessage[type] = 'Name must be at least 2 characters long';
            this.nameValidated[type] = true;
            return;
        }

        // Check if name contains at least first and last name (has space)
        if (!trimmedName.includes(' ')) {
            this.nameStatus[type] = 'invalid';
            this.nameMessage[type] = 'Please enter your full name (First Name and Last Name)';
            this.nameValidated[type] = true;
            return;
        }

        // Check if name contains only letters and spaces
        if (!/^[a-zA-Z\s]+$/.test(trimmedName)) {
            this.nameStatus[type] = 'invalid';
            this.nameMessage[type] = 'Name can only contain letters and spaces';
            this.nameValidated[type] = true;
            return;
        }

        // Check if name has proper format (at least 2 words with 2+ chars each)
        const nameParts = trimmedName.split(/\s+/).filter(part => part.length > 0);
        if (nameParts.length < 2) {
            this.nameStatus[type] = 'invalid';
            this.nameMessage[type] = 'Please enter both first name and last name';
            this.nameValidated[type] = true;
            return;
        }

        if (nameParts.some(part => part.length < 2)) {
            this.nameStatus[type] = 'invalid';
            this.nameMessage[type] = 'Each name part must be at least 2 characters';
            this.nameValidated[type] = true;
            return;
        }

        // Valid name
        this.nameStatus[type] = 'valid';
        this.nameMessage[type] = 'Name format is valid';
        this.nameValidated[type] = true;
    },
    validatePassword(password, type) {
        if (!password || password === '') {
            // Reset all criteria
            this.passwordCriteria[type] = {
                hasUpperCase: false,
                hasLowerCase: false,
                hasNumber: false,
                hasSpecialChar: false,
                minLength: false,
                hasValue: false
            };
            return;
        }

        // Check each criteria
        this.passwordCriteria[type] = {
            hasUpperCase: /[A-Z]/.test(password),
            hasLowerCase: /[a-z]/.test(password),
            hasNumber: /[0-9]/.test(password),
            hasSpecialChar: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password),
            minLength: password.length >= 8,
            hasValue: true
        };
    },
    isPasswordValid(type) {
        const criteria = this.passwordCriteria[type];
        return criteria.hasUpperCase && 
               criteria.hasLowerCase && 
               criteria.hasNumber && 
               criteria.hasSpecialChar && 
               criteria.minLength;
    },
    async validateReferralCode(code) {
        const statusEl = document.getElementById('referralCodeStatus');
        const messageEl = document.getElementById('referralCodeMessage');
        const inputEl = document.getElementById('referralCode');
        
        if (!statusEl || !messageEl || !inputEl) return;
        
        if (!code || code.trim() === '') {
            statusEl.classList.add('hidden');
            inputEl.classList.remove('border-green-500', 'border-red-500');
            return;
        }

        // Normalize code
        const normalizedCode = code.trim().toUpperCase();
        inputEl.value = normalizedCode;

        // Validate format
        if (!/^RSL\d+$/.test(normalizedCode)) {
            statusEl.classList.remove('hidden');
            messageEl.textContent = 'Invalid format. Use: RSL followed by numbers (e.g., RSL1001)';
            messageEl.className = 'text-sm text-red-600';
            inputEl.classList.remove('border-green-500');
            inputEl.classList.add('border-red-500');
            return;
        }

        // Check if referral code exists
        try {
            const response = await fetch(`/api/check-referral-code?code=${encodeURIComponent(normalizedCode)}`);
            const data = await response.json();
            
            statusEl.classList.remove('hidden');
            inputEl.classList.remove('border-red-500');
            
            if (data.valid) {
                messageEl.textContent = `✓ Valid referral code - You'll be linked to ${data.reseller_name || 'this reseller'}`;
                messageEl.className = 'text-sm text-green-600 font-semibold';
                inputEl.classList.add('border-green-500');
            } else {
                messageEl.textContent = 'Invalid referral code. Please check and try again.';
                messageEl.className = 'text-sm text-red-600';
                inputEl.classList.add('border-red-500');
            }
        } catch (error) {
            // Silently fail - validation will happen on server side
            statusEl.classList.add('hidden');
            inputEl.classList.remove('border-green-500', 'border-red-500');
        }
    }
}));

// KYC Form component
Alpine.data('kycForm', (initialIdType = '') => ({
    step: initialIdType ? 2 : 1,
    idType: initialIdType,
    showBackUpload: false,
    nextStep() {
        if (this.idType && this.idType !== '') {
            this.step = 2;
        } else {
            alert('Please select an ID type');
        }
    },
    prevStep() {
        if (this.step > 1) {
            this.step--;
        }
    },
    validateStep2() {
        const frontFile = document.querySelector('input[name="kyc_id_front"]');
        if (!frontFile || !frontFile.files || frontFile.files.length === 0) {
            alert('Please upload ID front image');
            return false;
        }
        
        if (this.idType === 'cnic' || this.idType === 'nicop') {
            const backFile = document.querySelector('input[name="kyc_id_back"]');
            if (!backFile || !backFile.files || backFile.files.length === 0) {
                alert('Please upload ID back image');
                return false;
            }
        }
        
        return true;
    },
    goToStep3() {
        if (this.validateStep2()) {
            this.step = 3;
        }
    },
    validateStep3() {
        const selfieFile = document.querySelector('input[name="kyc_selfie"]');
        if (!selfieFile || !selfieFile.files || selfieFile.files.length === 0) {
            alert('Please upload selfie with ID');
            return false;
        }
        return true;
    },
    submitForm(event) {
        if (!this.validateStep3()) {
            event.preventDefault();
            return false;
        }
        return true;
    },
    init() {
        if (this.idType && this.idType !== '') {
            this.step = 2;
        }
    }
}));

// Purchase flow component - moved to individual pages to avoid conflicts

// Login form component
Alpine.data('loginForm', function() {
    return {
        tooltip: null,
        selectedRole: '',
        isAdmin: false,
        init() {
            this.tooltip = null;
            this.selectedRole = '';
            this.isAdmin = false;
            // Check if email might be admin (optional - can be removed if not needed)
            // For now, we'll let the backend handle admin detection
        },
        selectRole(role) {
            this.selectedRole = role;
        },
        validateRole(event) {
            // Admin can login without role selection, but investor/reseller must select
            // This validation is also done on the backend
            if (!this.isAdmin && !this.selectedRole) {
                // Show error message (already shown via x-show)
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                return false;
            }
            // If validation passes, allow form to submit normally
            // Remove preventDefault to allow normal form submission
            if (event && event.type === 'submit') {
                // Form will submit normally since we're not preventing default
                return true;
            }
            return true;
        },
        showTooltip(tooltipName, event) {
            // Prevent event propagation
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            // Close other tooltips and toggle current one
            if (this.tooltip === tooltipName) {
                this.tooltip = null;
            } else {
                this.tooltip = tooltipName;
            }
        }
    };
});

// Image Viewer Modal Component (for KYC admin review)
Alpine.data('imageViewer', () => ({
    imageModal: { open: false, src: '', title: '' },
    openImage(src, title) {
        this.imageModal.src = src;
        this.imageModal.title = title;
        this.imageModal.open = true;
        document.body.style.overflow = 'hidden';
    },
    closeImage() {
        this.imageModal.open = false;
        this.imageModal.src = '';
        this.imageModal.title = '';
        document.body.style.overflow = '';
    }
}));

// Investor Dashboard Component
Alpine.data('investorDashboard', () => ({
    purchaseModalOpen: false,
    init() {
        // Check if URL has ?open=purchase parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('open') === 'purchase') {
            const self = this;
            setTimeout(function() {
                self.purchaseModalOpen = true;
            }, 0);
        }
    }
}));

// Scroll Animations using Intersection Observer
document.addEventListener('DOMContentLoaded', () => {
    // Animation observer options
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    // Create observer for scroll animations
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const animationType = entry.target.dataset.animation || 'fadeInUp';
                entry.target.classList.add('animated', animationType);
                animationObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Animate hero section immediately on page load (no scroll needed)
    const heroSection = document.querySelector('section:first-of-type');
    if (heroSection) {
        const heroElements = heroSection.querySelectorAll('.animate-on-scroll');
        heroElements.forEach((el) => {
            // Get delay from inline style or use default
            const delay = el.style.animationDelay ? parseFloat(el.style.animationDelay) * 1000 : 0;
            setTimeout(() => {
                const animationType = el.dataset.animation || 'fadeInUp';
                el.classList.add('animated', animationType);
            }, delay);
        });
    }

    // Observe all elements with animate-on-scroll class
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    animatedElements.forEach(el => {
        // Set initial state
        el.style.opacity = '0';
        animationObserver.observe(el);
    });

    // Add stagger delays to children in stagger containers
    const staggerContainers = document.querySelectorAll('[data-stagger]');
    staggerContainers.forEach(container => {
        const children = Array.from(container.children);
        children.forEach((child, index) => {
            // Only add if not already added
            if (!child.classList.contains('animate-on-scroll')) {
                child.classList.add('animate-on-scroll');
                child.dataset.animation = container.dataset.animation || 'fadeInUp';
                child.style.opacity = '0';
                child.style.transitionDelay = `${Math.min(index * 0.1, 0.6)}s`;
                animationObserver.observe(child);
            }
        });
    });

    // Counter animation for numbers
    function animateCounter(element, target, duration = 2000) {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = target + (element.textContent.includes('+') ? '+' : '');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(start) + (element.textContent.includes('+') ? '+' : '');
            }
        }, 16);
    }

    // Observe elements with counter animation
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const numberElement = entry.target.querySelector('[data-counter]');
                if (numberElement) {
                    const text = numberElement.textContent;
                    const number = parseInt(text.replace(/\D/g, ''));
                    if (number) {
                        numberElement.textContent = '0' + (text.includes('+') ? '+' : '');
                        animateCounter(numberElement, number, 2000);
                    }
                }
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    const counterElements = document.querySelectorAll('[data-animate-counter]');
    counterElements.forEach(el => {
        counterObserver.observe(el);
    });
});

// Start Alpine
window.Alpine = Alpine;
Alpine.start();
