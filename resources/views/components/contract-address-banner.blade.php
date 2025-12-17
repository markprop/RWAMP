@php
    $contractAddress = '0xFFC0e57D420867F53f5d968D493FdAd576156FAD';
@endphp

<!-- Alpine.js Component Registration -->
<script>
(function() {
    function registerContractBanner() {
        if (typeof Alpine !== 'undefined' && Alpine.data) {
            Alpine.data('contractBanner', function(address) {
                return {
                    copied: false,
                    copyError: false,
                    showWarning: false,
                    address: address,
                    showWarningDialog: function() {
                        this.showWarning = true;
                        // Prevent body scroll when modal is open
                        document.body.style.overflow = 'hidden';
                        document.body.classList.add('modal-open');
                    },
                    closeWarningDialog: function() {
                        this.showWarning = false;
                        // Restore body scroll
                        document.body.style.overflow = '';
                        document.body.classList.remove('modal-open');
                    },
                    copyToClipboard: function() {
                        var self = this;
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(self.address).then(function() {
                                self.copied = true;
                                self.copyError = false;
                                // Show warning dialog after copying
                                self.showWarningDialog();
                                setTimeout(function() {
                                    self.copied = false;
                                }, 3000);
                            }).catch(function(err) {
                                console.error('Clipboard API failed:', err);
                                self.fallbackCopy();
                            });
                        } else {
                            self.fallbackCopy();
                        }
                    },
                    fallbackCopy: function() {
                        var self = this;
                        try {
                            var textArea = document.createElement('textarea');
                            textArea.value = self.address;
                            textArea.style.position = 'fixed';
                            textArea.style.left = '-999999px';
                            textArea.style.top = '-999999px';
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            var successful = document.execCommand('copy');
                            document.body.removeChild(textArea);
                            if (successful) {
                                self.copied = true;
                                self.copyError = false;
                                // Show warning dialog after copying
                                self.showWarningDialog();
                                setTimeout(function() {
                                    self.copied = false;
                                }, 3000);
                            } else {
                                self.copyError = true;
                                setTimeout(function() {
                                    self.copyError = false;
                                }, 3000);
                            }
                        } catch (err) {
                            console.error('Fallback copy failed:', err);
                            self.copyError = true;
                            setTimeout(function() {
                                self.copyError = false;
                            }, 3000);
                        }
                    },
                    handleAddressClick: function() {
                        this.showWarningDialog();
                    }
                };
            });
        }
    }
    
    // Try to register immediately if Alpine is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', registerContractBanner);
    } else {
        registerContractBanner();
    }
    
    // Also listen for alpine:init event
    document.addEventListener('alpine:init', registerContractBanner);
})();
</script>

<!-- RWAMP Contract Address Banner -->
<div 
    class="rwamp-contract-banner fixed top-0 left-0 right-0 w-full bg-gradient-to-r from-red-600 via-red-700 to-red-600 text-white shadow-md z-[60] overflow-hidden border-b border-red-800/50" 
    x-data="contractBanner('{{ $contractAddress }}')"
    style="box-shadow: 0 2px 8px rgba(0,0,0,0.3); background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);"
>
    <!-- Animated Background Effect -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%), radial-gradient(circle at 80% 50%, rgba(255,255,255,0.1) 0%, transparent 50%); animation: pulse 4s ease-in-out infinite;"></div>
    </div>
    
    <div class="container mx-auto max-w-full px-0.5 sm:px-1.5 md:px-3">
        <div class="flex flex-row items-center justify-between sm:justify-center gap-0.5 sm:gap-1.5 md:gap-2 relative z-10 w-full py-0.5 sm:py-1">
            <!-- Left Section: Label and Address -->
            <div class="flex items-center gap-0.5 sm:gap-1 md:gap-1.5 flex-1 min-w-0 overflow-hidden">
                <!-- Label - Always visible, responsive text -->
                <div class="flex items-center gap-0.5 sm:gap-1 flex-shrink-0">
                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 md:w-3.5 md:h-3.5 text-yellow-200 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24" style="filter: drop-shadow(0 0 2px rgba(255,255,255,0.5));">
                        <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="inline-block text-[8px] sm:text-[9px] md:text-[10px] lg:text-xs xl:text-sm font-bold uppercase tracking-wide text-yellow-100 leading-tight whitespace-nowrap contract-label" style="text-shadow: 0 0 4px rgba(0,0,0,0.8), 0 1px 2px rgba(0,0,0,0.9);">
                        <span class="hidden sm:inline">Contract Address:</span>
                        <span class="sm:hidden">Contract:</span>
                    </span>
                </div>
                
                <!-- Contract Address Container -->
                <div 
                    @click="handleAddressClick()"
                    class="flex items-center gap-0.5 sm:gap-1 px-1 sm:px-1.5 py-0.25 sm:py-0.5 rounded bg-black/60 backdrop-blur-sm border border-yellow-300/90 hover:border-yellow-200 hover:bg-black/75 transition-all duration-200 group shadow-lg flex-1 min-w-0 max-w-full overflow-hidden cursor-pointer"
                    title="Click to view warning"
                >
                    <!-- Contract Address - Truncated on mobile with better visibility -->
                    <code 
                        class="contract-address-text text-[9px] sm:text-[10px] md:text-xs lg:text-sm font-mono font-bold text-yellow-50 select-all tracking-wide highlight-text leading-tight whitespace-nowrap overflow-hidden text-ellipsis block min-w-0 w-full pointer-events-none" 
                        id="contract-address"
                        title="{{ $contractAddress }}"
                        style="text-shadow: 0 0 6px rgba(0,0,0,1), 0 0 8px rgba(0,0,0,0.9), 0 1px 3px rgba(0,0,0,1), 0 0 12px rgba(255,255,255,0.3);"
                    >{{ $contractAddress }}</code>
                </div>
            </div>
            
            <!-- Right Section: Copy Button (Desktop-like positioning) -->
            <div class="flex items-center flex-shrink-0 ml-0.5 sm:ml-1 md:ml-2">
                <button 
                    @click="copyToClipboard()"
                    class="copy-btn p-0.5 sm:p-1 rounded hover:bg-yellow-400/40 active:bg-yellow-400/60 transition-colors duration-200 flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-red-600"
                    title="Copy contract address"
                    aria-label="Copy contract address"
                    :aria-pressed="copied"
                >
                    <!-- Copy Icon (default) -->
                    <svg 
                        x-show="!copied && !copyError" 
                        class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-yellow-100 hover:text-white transition-colors" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24" 
                        stroke-width="2.5" 
                        style="filter: drop-shadow(0 0 2px rgba(0,0,0,0.8));"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    
                    <!-- Success Checkmark Icon -->
                    <svg 
                        x-show="copied" 
                        class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-green-200" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24" 
                        stroke-width="2.5" 
                        x-cloak
                        style="filter: drop-shadow(0 0 2px rgba(0,0,0,0.8));"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                    </svg>
                    
                    <!-- Error Icon -->
                    <svg 
                        x-show="copyError" 
                        class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-red-200" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24" 
                        stroke-width="2.5" 
                        x-cloak
                        style="filter: drop-shadow(0 0 2px rgba(0,0,0,0.8));"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Success Toast Notification -->
    <div 
        x-show="copied"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-xl z-[70] flex items-center gap-2 text-xs sm:text-sm font-semibold toast-notification"
        x-cloak
    >
        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
        </svg>
        <span>Address Copied!</span>
    </div>
    
    <!-- Error Toast Notification -->
    <div 
        x-show="copyError"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-xl z-[70] flex items-center gap-2 text-xs sm:text-sm font-semibold toast-notification"
        x-cloak
    >
        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        <span>Failed to copy. Please try again.</span>
    </div>
    
    <!-- Warning Dialog Modal -->
    <div 
        x-show="showWarning"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.away="closeWarningDialog()"
        @keydown.escape.window="closeWarningDialog()"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        x-cloak
        style="background-color: rgba(0, 0, 0, 0.75); backdrop-filter: blur(4px);"
    >
        <div 
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="warning-dialog bg-gradient-to-br from-red-600 via-red-700 to-red-800 rounded-xl shadow-2xl border-2 border-yellow-300/90 max-w-lg w-full mx-4 overflow-hidden"
            style="box-shadow: 0 10px 40px rgba(0,0,0,0.5), 0 0 20px rgba(220,38,38,0.3), inset 0 1px 0 rgba(255,255,255,0.1);"
        >
            <!-- Dialog Header -->
            <div class="bg-gradient-to-r from-red-700 to-red-800 px-4 sm:px-6 py-3 sm:py-4 border-b border-yellow-300/50 flex items-center gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" style="filter: drop-shadow(0 0 4px rgba(0,0,0,0.8));">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-yellow-100 uppercase tracking-wide flex-1" style="text-shadow: 0 0 6px rgba(0,0,0,0.9), 0 2px 4px rgba(0,0,0,1);">
                    Important Warning
                </h3>
                <button 
                    @click="closeWarningDialog()"
                    class="flex-shrink-0 p-1.5 rounded-lg hover:bg-red-600/50 active:bg-red-600/70 transition-colors focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-red-700"
                    aria-label="Close warning dialog"
                >
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Dialog Body -->
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="space-y-3 sm:space-y-4">
                    <p class="text-sm sm:text-base text-yellow-50 leading-relaxed font-semibold" style="text-shadow: 0 0 4px rgba(0,0,0,0.8), 0 1px 2px rgba(0,0,0,0.9);">
                        <span class="text-yellow-200 font-bold">⚠️ DO NOT TRANSFER PAYMENT</span> to this address.
                    </p>
                    
                    <p class="text-xs sm:text-sm text-yellow-100 leading-relaxed" style="text-shadow: 0 0 3px rgba(0,0,0,0.8);">
                        This is a <span class="font-bold text-yellow-200">CONTRACT ADDRESS</span>, not a payment address. Sending funds directly to this contract address may result in permanent loss of your funds.
                    </p>
                    
                    <div class="bg-black/40 rounded-lg p-3 sm:p-4 border border-yellow-300/50 mt-4">
                        <p class="text-xs sm:text-sm text-yellow-50 leading-relaxed font-medium" style="text-shadow: 0 0 3px rgba(0,0,0,0.8);">
                            <span class="text-yellow-200 font-bold">✓ To make a payment:</span> Use the <span class="font-bold text-yellow-200">Purchase Page</span> on your dashboard through <span class="font-bold text-yellow-200">Wallet Connect</span>.
                        </p>
                    </div>
                    
                    <p class="text-xs sm:text-sm text-red-200 leading-relaxed font-semibold mt-4 pt-3 border-t border-yellow-300/30" style="text-shadow: 0 0 3px rgba(0,0,0,0.8);">
                        ⚠️ We are <span class="text-red-100 font-bold">NOT RESPONSIBLE</span> if you send funds to this contract address.
                    </p>
                </div>
            </div>
            
            <!-- Dialog Footer -->
            <div class="bg-gradient-to-r from-red-700/50 to-red-800/50 px-4 sm:px-6 py-3 sm:py-4 border-t border-yellow-300/50 flex justify-end">
                <button 
                    @click="closeWarningDialog()"
                    class="warning-dialog-btn px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg shadow-lg transition-all duration-200 text-xs sm:text-sm uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-red-700 active:scale-95 font-extrabold"
                    style="background: linear-gradient(135deg, #fde047 0%, #facc15 50%, #eab308 100%); color: #000000; box-shadow: 0 4px 12px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.5), 0 0 0 1px rgba(0,0,0,0.1); border: 1px solid rgba(234, 179, 8, 0.3);"
                >
                    I Understand
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Pulse animation for background effect */
@keyframes pulse {
    0%, 100% {
        opacity: 0.1;
    }
    50% {
        opacity: 0.15;
    }
}

/* Main banner container */
.rwamp-contract-banner {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    z-index: 60 !important;
    height: auto !important;
    /* Responsive height using clamp */
    min-height: clamp(26px, 4vw, 40px) !important;
    max-height: clamp(30px, 5vw, 44px) !important;
}

/* Ensure container doesn't overflow */
.rwamp-contract-banner .container {
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 0.125rem !important;
    padding-right: 0.125rem !important;
}

/* Mobile-specific container padding - minimal for better space usage */
@media (max-width: 640px) {
    .rwamp-contract-banner .container {
        padding-left: 0.125rem !important;
        padding-right: 0.125rem !important;
    }
    
    .rwamp-contract-banner > .container > div {
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}

@media (min-width: 641px) {
    .rwamp-contract-banner .container {
        padding-left: 0.375rem !important;
        padding-right: 0.375rem !important;
    }
}

/* Highlight text styling - consistent across all screen sizes */
.highlight-text {
    text-shadow: 0 0 4px rgba(0, 0, 0, 1), 0 0 6px rgba(0, 0, 0, 0.9), 0 1px 2px rgba(0, 0, 0, 1), 0 0 12px rgba(255, 255, 255, 0.2) !important;
    color: #fef08a !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
    letter-spacing: 0.05em !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Contract address text - responsive sizing with improved visibility */
.contract-address-text {
    letter-spacing: 0.03em !important;
    line-height: 1.3 !important;
    font-weight: 800 !important;
    color: #fef08a !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    /* Responsive font size using clamp - larger on mobile for visibility */
    font-size: clamp(9px, 3vw, 14px) !important;
    /* Ensure proper truncation */
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    display: block !important;
    min-width: 0 !important;
    width: 100% !important;
    /* Enhanced text shadow for better visibility */
    text-shadow: 0 0 6px rgba(0,0,0,1), 0 0 8px rgba(0,0,0,0.9), 0 1px 3px rgba(0,0,0,1), 0 0 12px rgba(255,255,255,0.3) !important;
}

/* Ensure left section uses available space properly */
.rwamp-contract-banner > .container > div > div:first-child {
    flex: 1 1 auto !important;
    min-width: 0 !important;
    overflow: hidden !important;
}

/* Ensure copy button stays on right */
.rwamp-contract-banner > .container > div > div:last-child {
    flex-shrink: 0 !important;
}

/* Contract label styling - always visible */
.contract-label {
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Ensure label container is always visible */
.rwamp-contract-banner > .container > div > div:first-child {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    flex-shrink: 0 !important;
}

/* Toast notification positioning - responsive */
.toast-notification {
    /* Position below banner - responsive top offset */
    top: clamp(26px, 5vw, 44px) !important;
    /* Center horizontally with max-width constraint */
    max-width: calc(100% - 1rem);
    white-space: nowrap;
    left: 50% !important;
    transform: translateX(-50%) !important;
}

/* Mobile toast adjustments */
@media (max-width: 480px) {
    .toast-notification {
        max-width: calc(100% - 0.5rem);
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}

/* Sidebar and Navbar positioning adjustments */
/* These will be dynamically adjusted via JavaScript, but provide fallback */
aside[style*="top: 40px"],
aside[style*="top: 36px"] {
    top: clamp(32px, 5vw, 40px) !important;
}

aside[style*="top: 36px"] .sidebar-header,
aside[style*="top: 40px"] .sidebar-header {
    padding-top: 1rem !important;
    margin-top: 0 !important;
}

nav[style*="top: 40px"],
nav[style*="top: 36px"] {
    top: clamp(32px, 5vw, 40px) !important;
    z-index: 50 !important;
}

/* Extra small screens (below 375px) - Very small mobile phones */
@media (max-width: 374px) {
    .rwamp-contract-banner {
        min-height: 28px !important;
        max-height: 32px !important;
    }
    
    .contract-address-text {
        font-size: 9px !important;
        max-width: 100% !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
        letter-spacing: 0.03em !important;
        /* Enhanced visibility */
        color: #fef08a !important;
        text-shadow: 0 0 8px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.95), 0 2px 4px rgba(0,0,0,1), 0 0 15px rgba(255,255,255,0.4) !important;
    }
    
    .rwamp-contract-banner .container {
        padding-left: 0.125rem !important;
        padding-right: 0.125rem !important;
    }
    
    .contract-label {
        font-size: 8px !important;
    }
    
    /* Minimize gaps on very small screens */
    .rwamp-contract-banner > .container > div {
        gap: 0.375rem !important;
        justify-content: space-between !important;
    }
    
    /* Left section spacing */
    .rwamp-contract-banner > .container > div > div:first-child {
        gap: 0.25rem !important;
    }
    
    /* Right section - copy button */
    .rwamp-contract-banner > .container > div > div:last-child {
        margin-left: 0.125rem !important;
    }
    
    .toast-notification {
        top: 32px !important;
        font-size: 9px !important;
        padding: 0.25rem 0.5rem !important;
    }
    
    .copy-btn {
        min-width: 20px !important;
        min-height: 20px !important;
        padding: 0.25rem !important;
    }
    
    .copy-btn svg {
        width: 14px !important;
        height: 14px !important;
    }
}

/* Small screens (375px - 480px) - Small mobile phones */
@media (min-width: 375px) and (max-width: 480px) {
    .rwamp-contract-banner {
        min-height: 30px !important;
        max-height: 34px !important;
    }
    
    .contract-address-text {
        font-size: 9px !important;
        max-width: 100% !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
        /* Enhanced visibility */
        color: #fef08a !important;
        text-shadow: 0 0 8px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.95), 0 2px 4px rgba(0,0,0,1), 0 0 15px rgba(255,255,255,0.4) !important;
    }
    
    .rwamp-contract-banner .container {
        padding-left: 0.25rem !important;
        padding-right: 0.25rem !important;
    }
    
    .contract-label {
        font-size: 9px !important;
    }
    
    /* Optimize spacing on small screens */
    .rwamp-contract-banner > .container > div {
        gap: 0.5rem !important;
        justify-content: space-between !important;
    }
    
    /* Left section spacing */
    .rwamp-contract-banner > .container > div > div:first-child {
        gap: 0.375rem !important;
    }
    
    /* Right section - copy button */
    .rwamp-contract-banner > .container > div > div:last-child {
        margin-left: 0.375rem !important;
    }
    
    .toast-notification {
        top: 34px !important;
        font-size: 10px !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .copy-btn {
        min-width: 22px !important;
        min-height: 22px !important;
    }
}

/* Medium-small screens (480px - 640px) - Large phones / Small tablets */
@media (min-width: 481px) and (max-width: 640px) {
    .rwamp-contract-banner {
        min-height: 32px !important;
        max-height: 36px !important;
    }
    
    .contract-address-text {
        font-size: 10px !important;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        /* Enhanced visibility */
        text-shadow: 0 0 8px rgba(0,0,0,1), 0 0 10px rgba(0,0,0,0.95), 0 2px 4px rgba(0,0,0,1), 0 0 15px rgba(255,255,255,0.35) !important;
    }
    
    .contract-label {
        font-size: 10px !important;
    }
    
    .toast-notification {
        top: 36px !important;
        font-size: 11px !important;
    }
}

/* Medium screens (640px - 768px) - Tablets */
@media (min-width: 641px) and (max-width: 768px) {
    .contract-address-text {
        font-size: 11px !important;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .rwamp-contract-banner {
        min-height: 32px !important;
        max-height: 36px !important;
    }
}

/* Ensure proper flex behavior on all screens */
@media (max-width: 640px) {
    .rwamp-contract-banner .container > div {
        flex-wrap: nowrap !important;
        overflow: hidden !important;
    }
    
    .rwamp-contract-banner .group {
        min-width: 0 !important;
        flex: 1 1 auto !important;
        max-width: 100% !important;
    }
}

/* Large screens (768px+) - Desktop */
@media (min-width: 769px) {
    .contract-address-text {
        font-size: 13px !important;
        /* Full address visible on desktop */
        max-width: none;
        overflow: visible;
        text-overflow: unset;
    }
    
    .rwamp-contract-banner {
        min-height: 36px !important;
        max-height: 40px !important;
    }
    
    .toast-notification {
        top: 40px !important;
    }
}

/* Ensure copy button is always accessible */
.copy-btn {
    min-width: 24px;
    min-height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Responsive copy button sizing */
@media (max-width: 374px) {
    .copy-btn {
        min-width: 18px !important;
        min-height: 18px !important;
        padding: 0.125rem !important;
    }
    
    .copy-btn svg {
        width: 12px !important;
        height: 12px !important;
    }
}

@media (min-width: 375px) and (max-width: 480px) {
    .copy-btn {
        min-width: 20px !important;
        min-height: 20px !important;
        padding: 0.25rem !important;
    }
    
    .copy-btn svg {
        width: 14px !important;
        height: 14px !important;
    }
}

/* Ensure address container doesn't overflow */
.rwamp-contract-banner > .container > div {
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
}

/* Improve address container visibility on mobile */
@media (max-width: 640px) {
    .rwamp-contract-banner .group {
        background-color: rgba(0, 0, 0, 0.75) !important;
        border-color: rgba(254, 240, 138, 0.95) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.6), inset 0 1px 2px rgba(255,255,255,0.15) !important;
    }
    
    .contract-address-text {
        /* Ensure high contrast on mobile */
        color: #fef08a !important;
        font-weight: 900 !important;
        /* Better line height for readability */
        line-height: 1.4 !important;
        /* Ensure full width usage */
        max-width: 100% !important;
    }
    
    /* Ensure label is visible and readable */
    .contract-label {
        color: #fef3c7 !important;
        font-weight: 900 !important;
        text-shadow: 0 0 6px rgba(0,0,0,0.9), 0 2px 4px rgba(0,0,0,1) !important;
    }
    
    /* Optimized spacing on mobile - minimal gaps */
    .rwamp-contract-banner > .container > div {
        gap: 0.5rem !important;
        padding-left: 0.25rem !important;
        padding-right: 0.25rem !important;
        justify-content: space-between !important;
    }
    
    /* Left section - label and address */
    .rwamp-contract-banner > .container > div > div:first-child {
        margin-left: 0 !important;
        flex: 1 1 auto !important;
        min-width: 0 !important;
        gap: 0.375rem !important;
    }
    
    /* Right section - copy button */
    .rwamp-contract-banner > .container > div > div:last-child {
        margin-right: 0 !important;
        flex-shrink: 0 !important;
        margin-left: 0.25rem !important;
    }
    
    /* Address container inside left section */
    .rwamp-contract-banner > .container > div > div:first-child > div:last-child {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }
}

/* Improve hover state for address container */
.rwamp-contract-banner .group:hover {
    border-color: rgba(254, 240, 138, 0.9) !important;
    background-color: rgba(0, 0, 0, 0.7) !important;
}

/* Accessibility improvements */
.copy-btn:focus-visible {
    outline: 2px solid #fef08a;
    outline-offset: 2px;
}

/* Print styles - hide banner when printing */
@media print {
    .rwamp-contract-banner {
        display: none !important;
    }
}

/* Warning Dialog Styles */
.warning-dialog {
    max-width: 90% !important;
    width: 100% !important;
}

@media (min-width: 640px) {
    .warning-dialog {
        max-width: 32rem !important;
    }
}

@media (min-width: 768px) {
    .warning-dialog {
        max-width: 36rem !important;
    }
}

/* Warning Dialog Button - Ensure text visibility with bright yellow background */
.warning-dialog-btn {
    background: linear-gradient(135deg, #fde047 0%, #facc15 50%, #eab308 100%) !important;
    color: #000000 !important;
    font-weight: 900 !important;
    text-shadow: none !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    border: 1px solid rgba(234, 179, 8, 0.3) !important;
}

.warning-dialog-btn:hover {
    transform: translateY(-1px);
    background: linear-gradient(135deg, #fef08a 0%, #fde047 50%, #facc15 100%) !important;
    box-shadow: 0 6px 16px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.5), 0 0 0 1px rgba(0,0,0,0.1) !important;
    color: #000000 !important;
    border-color: rgba(234, 179, 8, 0.5) !important;
}

.warning-dialog-btn:active {
    transform: translateY(0);
    background: linear-gradient(135deg, #facc15 0%, #eab308 50%, #ca8a04 100%) !important;
    color: #000000 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3), inset 0 1px 2px rgba(0,0,0,0.2) !important;
}

.warning-dialog-btn:focus {
    color: #000000 !important;
}

/* Ensure contract address container is clickable */
.rwamp-contract-banner .group {
    cursor: pointer !important;
}

.contract-address-text {
    user-select: text !important;
    pointer-events: none !important;
}

/* Mobile-specific warning dialog adjustments */
@media (max-width: 480px) {
    .warning-dialog {
        max-width: 95% !important;
        margin: 0.5rem !important;
    }
    
    .warning-dialog h3 {
        font-size: 0.875rem !important;
    }
    
    .warning-dialog p {
        font-size: 0.75rem !important;
    }
    
    .warning-dialog-btn {
        font-size: 0.625rem !important;
        padding: 0.5rem 1rem !important;
    }
}

/* Ensure modal backdrop is properly styled */
[ x-show="showWarning"] {
    z-index: 100 !important;
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden !important;
}
</style>

<!-- JavaScript to dynamically adjust sidebar/navbar positioning -->
<script>
(function() {
    'use strict';
    
    // Function to update sidebar and navbar positions based on banner height
    function updateLayoutPositions() {
        const banner = document.querySelector('.rwamp-contract-banner');
        if (!banner) return;
        
        // Get actual banner height
        const bannerHeight = banner.offsetHeight;
        
        // Update sidebar positions
        const sidebars = document.querySelectorAll('aside[style*="top"]');
        sidebars.forEach(sidebar => {
            sidebar.style.top = bannerHeight + 'px';
        });
        
        // Update navbar positions
        const navs = document.querySelectorAll('nav[style*="top"]');
        navs.forEach(nav => {
            nav.style.top = bannerHeight + 'px';
        });
        
        // Update toast notification position
        const toasts = document.querySelectorAll('.toast-notification');
        toasts.forEach(toast => {
            toast.style.top = bannerHeight + 'px';
        });
    }
    
    // Run on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateLayoutPositions);
    } else {
        updateLayoutPositions();
    }
    
    // Run on window resize (debounced)
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(updateLayoutPositions, 150);
    });
    
    // Run when banner is fully loaded (in case of dynamic content)
    const observer = new MutationObserver(function(mutations) {
        updateLayoutPositions();
    });
    
    const banner = document.querySelector('.rwamp-contract-banner');
    if (banner) {
        observer.observe(banner, {
            attributes: true,
            attributeFilter: ['style', 'class'],
            childList: true,
            subtree: true
        });
    }
})();
</script>
