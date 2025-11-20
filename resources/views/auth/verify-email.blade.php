@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP" class="w-16 h-16 mx-auto rounded-full mb-3">
                <h1 class="text-2xl font-montserrat font-bold">{{ __('Verify Your Email') }}</h1>
                <p class="text-gray-600 mt-2">
                    {{ __('We sent a 6-digit code to') }}<br>
                    <strong class="text-primary">{{ $email }}</strong>
                </p>
            </div>

            @if ($locked ?? false)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <p class="font-semibold">{{ __('Too Many Attempts') }}</p>
                    <p class="text-sm mt-1">
                        {{ __('Your email is temporarily locked. Please try again in :minutes minutes.', ['minutes' => ceil($lockRemaining / 60)]) }}
                    </p>
                </div>
            @else
                <!-- Error message container -->
                <div id="error-message" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4 hidden">
                    <span id="error-text"></span>
                </div>

                <!-- Success message container -->
                <div id="success-message" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4 hidden">
                    <span id="success-text"></span>
                </div>

                <form method="POST" action="{{ route('verify-email.post') }}" class="space-y-6" id="otp-form" x-data="window.otpVerification ? window.otpVerification() : {}" x-ref="form" @submit.prevent="submitAndClean">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    @if(config('app.debug') && isset($debugOtp))
                        <input type="hidden" id="debug-otp-sent" value="{{ $debugOtp }}">
                        <input type="hidden" id="debug-otp-cached" value="{{ $cachedOtp ?? '' }}">
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Enter Verification Code') }}
                        </label>
                        <input 
                            type="text" 
                            name="otp" 
                            id="otp-input"
                            x-model="otp"
                            @input="formatOtp"
                            maxlength="7"
                            pattern="[0-9 ]*"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="form-input w-full text-center text-2xl font-mono tracking-widest"
                            placeholder="000 000"
                            required
                            autofocus
                        >
                        <p class="text-xs text-gray-500 mt-2 text-center">
                            {{ __('Enter the 6-digit code sent to your email') }}
                        </p>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full btn-primary"
                        :disabled="isVerifyDisabled() || isSubmitting"
                        x-text="isSubmitting ? 'Verifying...' : 'Verify Email'"
                    >
                        {{ __('Verify Email') }}
                    </button>
                </form>

                <div class="mt-6 space-y-4">
                    <form method="POST" action="{{ route('verify-email.resend') }}" id="resend-form" x-data="window.resendTimer ? window.resendTimer() : {}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button 
                            type="submit" 
                            class="w-full px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg font-montserrat font-semibold transition-all duration-300 hover:border-primary hover:text-primary disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="countdown > 0"
                        >
                            <span x-show="countdown === 0">{{ __('Resend Code') }}</span>
                            <span x-show="countdown > 0" x-text="resendLabel()"></span>
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('register') }}" class="text-sm text-primary hover:underline">
                            {{ __('Change email?') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Make functions globally available for Alpine.js
window.otpVerification = function() {
    return {
        otp: '',
        isSubmitting: false,
        init() {
            // Get debug OTP values if available
            const debugOtpSent = document.getElementById('debug-otp-sent');
            const debugOtpCached = document.getElementById('debug-otp-cached');
            let sentOtp = debugOtpSent ? debugOtpSent.value : null;
            const cachedOtp = debugOtpCached ? debugOtpCached.value : null;
            
            // Log initialization
            const emailInput = document.querySelector('input[name="email"]');
            const email = emailInput ? emailInput.value : 'N/A';
            
            // Store sent OTP in sessionStorage for persistence
            if (sentOtp) {
                try {
                    sessionStorage.setItem('otp_sent_to_email', sentOtp);
                    sessionStorage.setItem('otp_sent_timestamp', new Date().toISOString());
                    sessionStorage.setItem('otp_sent_email', email);
                } catch (e) {
                    // Ignore sessionStorage errors
                }
            } else {
                // Try to get from sessionStorage if not in DOM
                try {
                    const storedSentOtp = sessionStorage.getItem('otp_sent_to_email');
                    if (storedSentOtp) {
                        sentOtp = storedSentOtp;
                    }
                } catch (e) {
                    // Ignore
                }
            }
        },
        stringToHex(str) {
            let hex = '';
            for (let i = 0; i < str.length; i++) {
                const charCode = str.charCodeAt(i);
                hex += charCode.toString(16).padStart(2, '0');
            }
            return hex;
        },
        // Return OTP without spaces (CSP-safe, no regex)
        strippedOtp() {
            const cleaned = String(this.otp || '').split(' ').join('');
            return cleaned;
        },
        isVerifyDisabled() {
            return this.strippedOtp().length !== 6;
        },
        formatOtp(event) {
            const originalValue = event.target.value || '';
            let value = String(originalValue).split(' ').join('');
            // Only allow numbers (CSP-safe, no regex)
            value = value.split('').filter(ch => ch >= '0' && ch <= '9').join('');
            // Limit to 6 digits
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            // Format with space after 3 digits
            let formattedValue = value;
            if (value.length > 3) {
                formattedValue = value.substring(0, 3) + ' ' + value.substring(3, 6);
            }
            this.otp = formattedValue;
            event.target.value = formattedValue;
        },
        submitAndClean() {
            // Replace the input value with the 6-digit numeric OTP before submit
            const input = document.getElementById('otp-input');
            const rawOtp = this.otp || '';
            const cleanedOtp = this.strippedOtp();
            const emailInput = document.querySelector('input[name="email"]');
            const email = emailInput ? emailInput.value : 'N/A';
            
            // Get the OTP that was sent to email
            let sentOtp = null;
            try {
                sentOtp = sessionStorage.getItem('otp_sent_to_email');
            } catch (e) {
                // Ignore
            }
            
            if (input) {
                input.value = cleanedOtp;
            }
            
            // Hide previous messages
            this.hideMessages();
            
            // Set submitting state
            this.isSubmitting = true;
            
            // Prepare form data
            const formData = new FormData(this.$refs.form);
            
            // Submit via AJAX
            fetch(this.$refs.form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                
                // Check if response is JSON
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        return { type: 'json', data: data, status: response.status };
                    });
                } else {
                    // Handle non-JSON response (redirect or HTML)
                    return response.text().then(html => {
                        return { type: 'html', data: html, status: response.status };
                    });
                }
            })
            .then(result => {
                this.isSubmitting = false;
                
                // Handle JSON response
                if (result.type === 'json') {
                    const data = result.data;
                    
                    // Handle success response
                    if (data.success && data.redirect) {
                        this.showSuccess(data.message || 'Email verified successfully! Redirecting...');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                        return;
                    }
                    
                    // Handle error response
                    let errorMessage = data.message || 'Invalid verification code.';
                    
                    // Add debug info if available
                    if (data.debug) {
                        if (!data.debug.cache_exists) {
                            errorMessage += ' (OTP not found in cache - may have expired)';
                        } else if (data.debug.cached_otp !== data.debug.submitted_otp) {
                            errorMessage += ' (OTP mismatch: expected ' + data.debug.cached_otp + ', got ' + data.debug.submitted_otp + ')';
                        }
                    }
                    
                    if (data.errors && data.errors.otp) {
                        this.showError(data.errors.otp[0] || errorMessage);
                    } else {
                        this.showError(errorMessage);
                    }
                    return;
                }
                
                // Handle HTML/redirect response (fallback for non-AJAX)
                if (result.status >= 200 && result.status < 300) {
                    // Success - reload page to get redirect
                    this.showSuccess('Email verified successfully! Redirecting...');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.showError('An error occurred. Please try again.');
                }
            })
            .catch(error => {
                this.isSubmitting = false;
                this.showError('An error occurred. Please try again.');
            });
        },
        showError(message) {
            const errorDiv = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            const successDiv = document.getElementById('success-message');
            
            if (errorDiv && errorText) {
                errorText.textContent = message;
                errorDiv.classList.remove('hidden');
                successDiv.classList.add('hidden');
                
                // Scroll to error
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },
        showSuccess(message) {
            const successDiv = document.getElementById('success-message');
            const successText = document.getElementById('success-text');
            const errorDiv = document.getElementById('error-message');
            
            if (successDiv && successText) {
                successText.textContent = message;
                successDiv.classList.remove('hidden');
                errorDiv.classList.add('hidden');
                
                // Scroll to success message
                successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },
        hideMessages() {
            const errorDiv = document.getElementById('error-message');
            const successDiv = document.getElementById('success-message');
            
            if (errorDiv) errorDiv.classList.add('hidden');
            if (successDiv) successDiv.classList.add('hidden');
        }
    }
}

window.resendTimer = function() {
    return {
        countdown: 60,
        resendLabel() {
            return 'Resend code (' + this.countdown + 's)';
        },
        init() {
            // Start countdown from 60 seconds
            const interval = setInterval(() => {
                if (this.countdown > 0) {
                    this.countdown--;
                } else {
                    clearInterval(interval);
                }
            }, 1000);
            
            // Reset countdown when form is submitted successfully
            document.getElementById('resend-form')?.addEventListener('submit', () => {
                this.countdown = 60;
                const newInterval = setInterval(() => {
                    if (this.countdown > 0) {
                        this.countdown--;
                    } else {
                        clearInterval(newInterval);
                    }
                }, 1000);
            });
        }
    }
}

</script>
@endpush

