// Phone Input Initialization using intl-tel-input
// Automatically initializes all .phone-input elements

(function() {
    'use strict';

    // Wait for intlTelInput library to load
    function waitForIntlTelInput(callback, maxRetries = 40) {
        let retries = 0;
        const checkInterval = setInterval(() => {
            if (typeof window.intlTelInput === 'function') {
                clearInterval(checkInterval);
                callback();
            } else {
                retries++;
                if (retries >= maxRetries) {
                    clearInterval(checkInterval);
                    console.warn('[PhoneInput] intl-tel-input library failed to load');
                }
            }
        }, 250);
    }

    function initializePhoneInputs() {
        const phoneInputs = document.querySelectorAll('.phone-input');
        console.log('[PhoneInput] found inputs:', phoneInputs.length);

        phoneInputs.forEach((input) => {
            // Skip if already initialized
            if (input.dataset.phoneInitialized === '1') {
                return;
            }

            // Find the hidden input for this phone input
            const form = input.closest('form');
            if (!form) {
                console.warn('[PhoneInput] Phone input must be inside a form', input);
                return;
            }

            const hiddenInput = form.querySelector('.phone-hidden[data-phone-hidden]');
            if (!hiddenInput) {
                console.warn('[PhoneInput] Hidden phone input not found for', input);
                return;
            }

            console.log('[PhoneInput] initializing intlTelInput for', input.name || input.id);

            // Initialize intl-tel-input
            const iti = window.intlTelInput(input, {
                // IMPORTANT: Do NOT use geoIpLookup here because the
                // site's Content Security Policy (CSP) blocks external
                // calls to services like ipapi.co.
                // We default to Pakistan and still allow the user to
                // change the country from the dropdown.
                initialCountry: 'pk',
                preferredCountries: ['pk', 'us', 'gb'],
                separateDialCode: true,
                nationalMode: false,
                autoPlaceholder: 'polite',
                utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/utils.js'
            });

            // Simple debug hook to see when dropdown toggles
            input.addEventListener('click', () => {
                const wrapper = input.closest('.iti');
                console.log('[PhoneInput] click, wrapper classes:', wrapper && wrapper.className);
            });

            // Mark as initialized
            input.dataset.phoneInitialized = '1';

            // Handle form submission (no browser alerts - inline messaging instead)
            form.addEventListener('submit', function (e) {
                // Forms that manage phone validation themselves (Alpine) can opt‑out
                if (form.hasAttribute('data-phone-managed')) {
                    if (iti.isValidNumber()) {
                        hiddenInput.value = iti.getNumber();
                    }
                    return;
                }

                const wrapper = input.closest('.phone-input-wrapper') || input.parentElement;
                let errorEl = wrapper ? wrapper.querySelector('.phone-error-message') : null;

                const showError = (message) => {
                    if (!wrapper) return;
                    if (!errorEl) {
                        errorEl = document.createElement('p');
                        errorEl.className = 'phone-error-message text-xs text-red-500 mt-1';
                        // Insert after wrapper
                        if (wrapper.parentNode) {
                            wrapper.parentNode.insertBefore(errorEl, wrapper.nextSibling);
                        } else {
                            wrapper.appendChild(errorEl);
                        }
                    }
                    errorEl.textContent = message;
                    wrapper.classList.add('ring-2', 'ring-red-500/30', 'border-red-500');
                };

                const clearError = () => {
                    if (wrapper) {
                        wrapper.classList.remove('ring-2', 'ring-red-500/30', 'border-red-500');
                    }
                    if (errorEl) {
                        errorEl.textContent = '';
                    }
                };

                if (!iti.isValidNumber()) {
                    e.preventDefault();
                    showError('Please enter a valid phone number.');
                    input.focus();
                    return false;
                }

                clearError();

                // Save phone number in E.164 international format
                const e164Number = iti.getNumber();
                hiddenInput.value = e164Number;
            });

            // Optional: Update hidden input on blur for real-time feedback
            input.addEventListener('blur', function() {
                if (iti.isValidNumber()) {
                    hiddenInput.value = iti.getNumber();
                }
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            waitForIntlTelInput(initializePhoneInputs);
        });
    } else {
        waitForIntlTelInput(initializePhoneInputs);
    }

    // Re-initialize on dynamic content (for AJAX-loaded forms)
    const observer = new MutationObserver(function(mutations) {
        let shouldReinit = false;
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.classList && node.classList.contains('phone-input')) {
                            shouldReinit = true;
                        } else if (node.querySelector && node.querySelector('.phone-input')) {
                            shouldReinit = true;
                        }
                    }
                });
            }
        });
        if (shouldReinit) {
            console.log('[PhoneInput] mutation observed – reinitializing');
            waitForIntlTelInput(initializePhoneInputs);
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();
