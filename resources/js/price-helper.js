/**
 * Price Helper for Multi-Currency Display
 * Calculates USD and AED equivalents from PKR values
 * 
 * IMPORTANT: These functions must be on window object for Alpine.js CSP mode
 */

// Initialize immediately to ensure functions are available
(function() {
    'use strict';
    
    // Get exchange rates from server (injected via meta tag or global)
    window.getExchangeRates = function() {
        // Try to get from meta tag first
        const usdPkrMeta = document.querySelector('meta[name="exchange-rate-usd-pkr"]');
        const aedPkrMeta = document.querySelector('meta[name="exchange-rate-aed-pkr"]');
        
        if (usdPkrMeta && aedPkrMeta) {
            return {
                usdPkr: parseFloat(usdPkrMeta.getAttribute('content')) || 278,
                aedPkr: parseFloat(aedPkrMeta.getAttribute('content')) || 75.7
            };
        }
        
        // Fallback to default rates
        return {
            usdPkr: 278,
            aedPkr: 75.7
        };
    };

    /**
     * Format price with multi-currency display
     * @param {number} pkr - Price in PKR
     * @param {object} options - Formatting options
     * @returns {string} HTML string with formatted prices
     */
    window.formatPriceTag = function(pkr, options = {}) {
        if (!pkr || pkr <= 0) return '<span class="text-gray-400">—</span>';
        
        const rates = window.getExchangeRates();
        const size = options.size || 'normal';
        const variant = options.variant || 'light';
        
        const usd = rates.usdPkr > 0 ? pkr / rates.usdPkr : 0;
        const aed = rates.aedPkr > 0 ? pkr / rates.aedPkr : 0;
        
        const pkrFormatted = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(pkr);
        
        const usdFormatted = usd > 0 ? new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(usd) : null;
        
        const aedFormatted = aed > 0 ? new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(aed) : null;
        
        // Size classes
        const primarySizeClass = size === 'small' ? 'text-sm' : size === 'large' ? 'text-2xl md:text-3xl' : 'text-base md:text-lg';
        const secondarySizeClass = size === 'small' ? 'text-xs' : size === 'large' ? 'text-sm' : 'text-xs';
        
        // Color classes
        const primaryColorClass = variant === 'dark' ? 'text-white' : 'text-gray-900';
        const secondaryColorClass = variant === 'dark' ? 'text-white/80' : 'text-gray-500';
        
        let html = `<div class="price-tag ${options.class || ''}">`;
        
        // Primary: USD (if available), fallback to PKR
        if (usdFormatted) {
            html += `<div class="font-bold ${primarySizeClass} ${primaryColorClass}">USD $${usdFormatted}</div>`;
            
            // Secondary: AED and PKR
            if (aedFormatted || pkrFormatted) {
                html += `<div class="mt-0.5 sm:mt-1 flex flex-col sm:flex-row sm:items-center sm:gap-1.5 ${secondarySizeClass} ${secondaryColorClass}">`;
                if (aedFormatted) {
                    html += `<span class="whitespace-nowrap">AED ${aedFormatted}</span>`;
                }
                if (aedFormatted && pkrFormatted) {
                    html += `<span class="hidden sm:inline">·</span>`;
                }
                if (pkrFormatted) {
                    html += `<span class="whitespace-nowrap">PKR ${pkrFormatted}</span>`;
                }
                html += `</div>`;
            }
        } else {
            // Fallback: PKR as primary if USD not available
            html += `<div class="font-bold ${primarySizeClass} ${primaryColorClass}">PKR ${pkrFormatted}</div>`;
            
            // Secondary: AED if available
            if (aedFormatted) {
                html += `<div class="mt-0.5 sm:mt-1 ${secondarySizeClass} ${secondaryColorClass}">`;
                html += `<span class="whitespace-nowrap">AED ${aedFormatted}</span>`;
                html += `</div>`;
            }
        }
        
        html += `</div>`;
        return html;
    };

    /**
     * Alpine.js helper function for price formatting
     * Usage: x-html="formatPriceTag(pkrValue)"
     */
    window.formatPriceTagAlpine = function(pkr) {
        return window.formatPriceTag(pkr, { size: 'normal', variant: 'light' });
    };
    
    // Ensure functions are available immediately
    if (typeof window !== 'undefined') {
        // Functions are already assigned above, this is just a safety check
        if (!window.formatPriceTag) {
            console.warn('formatPriceTag not initialized properly');
        }
    }
})();

