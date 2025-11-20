import './bootstrap';
import Alpine from '@alpinejs/csp';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

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

// Reseller form handling
Alpine.data('resellerForm', () => ({
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
                data = await response.json(); 
            } catch (e) {
                console.error('JSON parse error:', e);
            }

            if (response.ok && data && data.success) {
                this.success = true;
                this.formData = { 
                    name: '', 
                    email: '', 
                    phone: '', 
                    company: '', 
                    investmentCapacity: '', 
                    message: '', 
                    hp: '' 
                };
                // Scroll to success message
                setTimeout(() => {
                    const successEl = document.querySelector('[x-show="success"]');
                    if (successEl) successEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            } else {
                this.error = true;
                this.errorMessage = data?.message || 'Something went wrong. Please try again later.';
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.error = true;
            this.errorMessage = 'Network error. Please check your connection and try again.';
        } finally {
            this.loading = false;
        }
    }
}));

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
Alpine.data('loginForm', () => ({
    tooltip: null,
    init() {
        this.tooltip = null;
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
}));

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

// Start Alpine
window.Alpine = Alpine;
Alpine.start();
