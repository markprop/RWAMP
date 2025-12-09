@php
    $presaleData = $presaleData ?? [];
    $tokenPriceUsd = $presaleData['token_price_usd'] ?? 0.01;
    $totalRaisedUsd = $presaleData['total_raised_usd'] ?? 0;
    $totalTokensSold = $presaleData['total_tokens_sold'] ?? 0;
    $maxSupply = $presaleData['max_supply'] ?? 1000000000;
    $presaleStage = $presaleData['stage'] ?? 2;
    $presaleBonus = $presaleData['bonus_percentage'] ?? 10;
    $minPurchaseUsd = $presaleData['min_purchase_usd'] ?? 55;
    
    // Auto-calculate supply progress to ensure accuracy
    $supplyProgress = $maxSupply > 0 ? (($totalTokensSold / $maxSupply) * 100) : 0;
    $supplyProgress = min(100, max(0, round($supplyProgress, 2))); // Clamp between 0-100 and round to 2 decimals
@endphp

<div class="presale-container-wrapper">
<div class="presale-container" 
     x-data="window.presaleSection ? window.presaleSection() : {}"
     x-init="init()">
    <!-- Stage Indicator -->
    <div class="flex items-center justify-center mb-4">
        <div class="flex items-center gap-2 bg-gradient-to-r from-red-500/30 to-yellow-500/30 backdrop-blur-md border-2 border-red-400/60 rounded-full px-4 py-2 shadow-lg animate-pulse-slow">
            <div class="w-2.5 h-2.5 bg-red-400 rounded-full animate-ping shadow-lg shadow-red-400/70"></div>
            <span class="text-white font-black text-xs md:text-sm animate-text-glow tracking-wide">🔥 STAGE {{ $presaleStage }} IS LIVE 🔥</span>
        </div>
    </div>

    <!-- Price Box -->
    <div class="presale-card mb-4 animate-fade-in">
        <div class="text-center">
            <div class="text-white text-sm mb-2 font-black animate-text-glow flex items-center justify-center gap-2">
                <span>💰</span>
                <span>1 RWAMP Price</span>
            </div>
            <div class="text-4xl md:text-5xl font-black text-white animate-number animate-text-shine drop-shadow-lg overflow-hidden" style="font-weight: 900; letter-spacing: -0.02em;">
                @php
                    $tokenPricePkr = $presaleData['token_price_pkr'] ?? ($tokenPriceUsd * ($presaleData['usd_to_pkr_rate'] ?? 278));
                @endphp
                @include('components.price-tag', [
                    'pkr' => $tokenPricePkr,
                    'size' => 'large',
                    'variant' => 'dark',
                    'class' => 'text-white [&_.text-gray-900]:text-white [&_.text-gray-500]:text-white/80 w-full'
                ])
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <!-- Total Raised -->
        <div class="presale-card animate-fade-in-delay-1">
            <div class="text-white text-xs mb-1.5 font-black flex items-center gap-1">
                <span>💵</span>
                <span>Total Raised</span>
            </div>
            <div class="text-xl md:text-2xl font-black text-white animate-text-glow drop-shadow-md overflow-hidden" style="font-weight: 900;">
                @php
                    $totalRaisedPkr = $presaleData['total_raised_pkr'] ?? ($totalRaisedUsd * ($presaleData['usd_to_pkr_rate'] ?? 278));
                @endphp
                @include('components.price-tag', [
                    'pkr' => $totalRaisedPkr,
                    'size' => 'normal',
                    'variant' => 'dark',
                    'class' => 'text-white [&_.text-gray-900]:text-white [&_.text-gray-500]:text-white/80 w-full'
                ])
            </div>
        </div>
        
        <!-- Tokens Sold -->
        <div class="presale-card animate-fade-in-delay-2">
            <div class="text-white text-xs mb-1.5 font-black flex items-center gap-1">
                <span>🪙</span>
                <span>Tokens Sold</span>
            </div>
            <div class="text-xl md:text-2xl font-black text-white animate-text-glow drop-shadow-md" style="font-weight: 900;">{{ number_format($totalTokensSold, 0) }} RWAMP</div>
        </div>
    </div>

    <!-- Bonus and Token Name -->
    <div class="text-center mb-4 animate-fade-in-delay-3">
        <div class="text-white font-black text-base md:text-lg mb-1 animate-pulse-slow animate-text-glow drop-shadow-lg">🎁 {{ $presaleBonus }}% Bonus Active 🎁</div>
        <div class="text-white text-sm md:text-base font-black">RWAMP (RWAMP):</div>
    </div>

    <!-- Supply Progress -->
    <div class="presale-card mb-4 animate-fade-in-delay-4">
        <div class="mb-3">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-white font-black text-xs animate-text-glow flex items-center gap-1">
                    <span>📊</span>
                    <span>Supply Progress</span>
                </span>
                <span class="text-white font-black text-xs animate-text-glow" style="font-weight: 900;" x-text="(supplyProgress || 0).toFixed(2) + '% Complete'"></span>
            </div>
            <div class="text-white text-xs mb-3 font-black" style="font-weight: 900;">
                {{ number_format($totalTokensSold, 0) }} RWAMP of {{ number_format($maxSupply, 0) }} RWAMP
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="relative h-5 bg-gray-900/80 rounded-full overflow-hidden border-2 border-gray-700/60 shadow-inner">
            <div 
                id="supply-progress-bar"
                class="absolute inset-y-0 left-0 bg-gradient-to-r from-red-500 via-yellow-500 to-red-500 rounded-full shadow-lg shadow-red-500/50 progress-bar-animated"
                :style="'width: ' + (supplyProgress || 0) + '%'"
                :class="{ 'opacity-0': (supplyProgress || 0) === 0, 'opacity-100': (supplyProgress || 0) > 0 }"
                style="--progress-width: {{ $supplyProgress }}%;">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
            </div>
        </div>
    </div>

    <!-- BUY TOKEN NOW Button -->
    <div class="text-center animate-fade-in-delay-5">
        <button 
            @click="buyTokenNow()"
            class="presale-buy-button bg-gradient-to-r from-red-600 to-red-500 text-white px-6 py-3.5 rounded-xl font-black text-base md:text-lg hover:from-red-700 hover:to-red-600 flex items-center gap-2 mx-auto w-full justify-center shadow-xl border-2 border-red-400/50 animate-bounce-subtle">
            <span class="animate-text-shine text-lg">🚀 BUY TOKEN NOW 🚀</span>
        </button>
        <div class="mt-2.5 text-white text-xs font-black animate-text-glow">💳 Minimum Buy Coins of 1000</div>
    </div>
</div>
</div>

<script>
function presaleSection() {
    return {
        selectedPaymentMethod: 'USDT', // Default to USDT
        supplyProgress: 0, // Start at 0 for animation
        
        init() {
            // Get target progress from server - ensure it's a valid number
            const serverProgress = {{ $supplyProgress }};
            const targetProgress = typeof serverProgress === 'number' ? serverProgress : parseFloat(serverProgress) || 0;
            
            // Ensure target is valid (0-100)
            const validTarget = Math.max(0, Math.min(100, targetProgress));
            
            // Start from 0 for animation
            this.supplyProgress = 0;
            
            // Wait for Alpine to initialize, then animate
            this.$nextTick(() => {
                // Use requestAnimationFrame for smooth animation
                const duration = 2000; // 2 seconds for smooth animation
                const startTime = performance.now();
                const startValue = 0;
                
                const animate = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Use easing function for smooth animation
                    const easeOutCubic = 1 - Math.pow(1 - progress, 3);
                    const currentValue = startValue + (validTarget - startValue) * easeOutCubic;
                    
                    // Update Alpine.js reactive property - this will update the :style binding
                    this.supplyProgress = parseFloat(currentValue.toFixed(2));
                    
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    } else {
                        // Ensure final value is exactly the target
                        this.supplyProgress = validTarget;
                    }
                };
                
                // Start animation after a small delay
                setTimeout(() => {
                    requestAnimationFrame(animate);
                }, 200);
            });
        },
        
        selectPaymentMethod(method) {
            this.selectedPaymentMethod = method;
        },
        
        buyTokenNow() {
            // Check if user is authenticated (check for auth token or user data)
            const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
            
            if (!isAuthenticated) {
                // Redirect to login with intended=purchase parameter
                window.location.href = '{{ route("login", ["intended" => "purchase"]) }}';
                return;
            }
            
            // Trigger the purchase modal with default USDT/TRC20
            window.dispatchEvent(new CustomEvent('open-purchase-modal', { 
                detail: { network: 'TRC20', method: 'USDT' } 
            }));
        }
    }
}

// Make it globally available for Alpine.js
window.presaleSection = presaleSection;

// Fallback: Direct DOM manipulation if Alpine.js doesn't work
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const progressBar = document.getElementById('supply-progress-bar') || document.querySelector('.progress-bar-animated');
        if (progressBar) {
            const targetProgress = {{ $supplyProgress }};
            const validProgress = Math.max(0, Math.min(100, targetProgress));
            
            // Check if Alpine.js already handled it by checking if width is set
            const currentWidth = progressBar.style.width;
            if (currentWidth && parseFloat(currentWidth) > 0) {
                return; // Already set by Alpine.js
            }
            
            // Start from 0
            progressBar.style.width = '0%';
            
            // Animate using requestAnimationFrame
            const duration = 2000;
            const startTime = performance.now();
            const startValue = 0;
            
            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Use easing function for smooth animation
                const easeOutCubic = 1 - Math.pow(1 - progress, 3);
                const currentValue = startValue + (validProgress - startValue) * easeOutCubic;
                
                progressBar.style.width = currentValue.toFixed(2) + '%';
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    // Ensure final value
                    progressBar.style.width = validProgress + '%';
                }
            };
            
            // Start animation after a small delay
            setTimeout(() => {
                requestAnimationFrame(animate);
            }, 200);
        }
    }, 500);
});
</script>

<style>
.presale-container-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}

.presale-container {
    --border-angle: 0turn;
    --main-bg: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(234, 179, 8, 0.1) 100%);
    
    backdrop-filter: blur(12px);
    border: solid 4px transparent;
    border-radius: 1.5rem;
    padding: 1.5rem;
    
    --gradient-border: conic-gradient(
        from var(--border-angle),
        /* Light Red */
        rgba(239, 68, 68, 0.4) 0deg,
        rgba(220, 38, 38, 0.5) 45deg,
        /* Transition to Yellow */
        rgba(234, 179, 8, 0.5) 90deg,
        rgba(251, 191, 36, 0.6) 135deg,
        /* Light Yellow */
        rgba(250, 204, 21, 0.5) 180deg,
        /* Transition back to Red */
        rgba(234, 179, 8, 0.5) 225deg,
        rgba(220, 38, 38, 0.5) 270deg,
        /* Back to Light Red */
        rgba(239, 68, 68, 0.4) 315deg,
        rgba(220, 38, 38, 0.5) 360deg
    );
    
    background: 
        var(--main-bg) padding-box,
        var(--gradient-border) border-box;
    
    background-clip: padding-box, border-box;
    background-origin: padding-box, border-box;
    box-shadow: 0 20px 60px rgba(220, 38, 38, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    animation: container-glow 3s ease-in-out infinite, bg-spin 8s linear infinite;
    position: relative;
    overflow: visible;
}

.border-pointer {
    display: none;
}

@keyframes container-glow {
    0%, 100% {
        box-shadow: 0 20px 60px rgba(220, 38, 38, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }
    50% {
        box-shadow: 0 20px 60px rgba(234, 179, 8, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }
}

@keyframes bg-spin {
    0% {
        --border-angle: 0turn;
    }
    100% {
        --border-angle: 1turn;
    }
}

@property --border-angle {
    syntax: "<angle>";
    inherits: true;
    initial-value: 0turn;
}

.presale-card {
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(234, 179, 8, 0.1) 100%);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(220, 38, 38, 0.4);
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.15);
    transition: all 0.3s ease;
    overflow: hidden;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.presale-card:hover {
    border-color: rgba(234, 179, 8, 0.4);
    box-shadow: 0 6px 16px rgba(234, 179, 8, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.15);
    transform: translateY(-2px);
}

@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.9;
        transform: scale(1.02);
    }
}

.animate-pulse-slow {
    animation: pulse-slow 3s ease-in-out infinite;
}

/* Fade in animations */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

.animate-fade-in-delay-1 {
    animation: fade-in 0.6s ease-out 0.1s both;
}

.animate-fade-in-delay-2 {
    animation: fade-in 0.6s ease-out 0.2s both;
}

.animate-fade-in-delay-3 {
    animation: fade-in 0.6s ease-out 0.3s both;
}

.animate-fade-in-delay-4 {
    animation: fade-in 0.6s ease-out 0.4s both;
}

.animate-fade-in-delay-5 {
    animation: fade-in 0.6s ease-out 0.5s both;
}

/* Number animation */
@keyframes number-pop {
    0% {
        transform: scale(0.8);
        opacity: 0;
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-number {
    animation: number-pop 0.8s ease-out;
}

/* Progress bar animations */
.progress-bar-animated {
    position: relative;
    overflow: visible !important;
    will-change: width;
    min-width: 0 !important;
    max-width: 100% !important;
    z-index: 1;
    display: block !important;
    visibility: visible !important;
    box-sizing: border-box !important;
    height: 100% !important;
    /* No CSS transition - JavaScript handles animation via requestAnimationFrame */
}

/* Ensure progress bar is visible even at low percentages */
.presale-card .progress-bar-animated {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Fallback using CSS variable if Alpine.js doesn't work */
.progress-bar-animated[style*="--progress-width"] {
    width: var(--progress-width) !important;
}

/* Initial width from CSS variable - will be overridden by inline styles */
#supply-progress-bar:not([style*="width"]) {
    width: var(--progress-width, 0%) !important;
}

/* Ensure the progress bar container doesn't clip small values */
.presale-card .relative {
    overflow: hidden !important;
}

/* Ensure inline styles always take precedence */
#supply-progress-bar[style*="width"] {
    /* Inline style width will be used, this is just a fallback */
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
}

/* Bounce subtle animation */
@keyframes bounce-subtle {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}

.animate-bounce-subtle {
    animation: bounce-subtle 2s ease-in-out infinite;
}

/* Rocket animation */
@keyframes rocket {
    0%, 100% {
        transform: translateX(0) rotate(0deg);
    }
    25% {
        transform: translateX(3px) rotate(-5deg);
    }
    75% {
        transform: translateX(-3px) rotate(5deg);
    }
}

.animate-rocket {
    animation: rocket 1.5s ease-in-out infinite;
}

/* Text glow animation */
@keyframes text-glow {
    0%, 100% {
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.8), 0 0 20px rgba(255, 255, 255, 0.5), 0 0 30px rgba(234, 179, 8, 0.4);
    }
    50% {
        text-shadow: 0 0 20px rgba(255, 255, 255, 1), 0 0 40px rgba(255, 255, 255, 0.7), 0 0 60px rgba(234, 179, 8, 0.6), 0 0 80px rgba(220, 38, 38, 0.4);
    }
}

.animate-text-glow {
    animation: text-glow 2s ease-in-out infinite;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Extra bold numbers */
.presale-card [class*="text-"] {
    font-weight: 900 !important;
}

.presale-card .font-black {
    font-weight: 900 !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Text shine animation */
@keyframes text-shine {
    0% {
        background-position: -200% center;
    }
    100% {
        background-position: 200% center;
    }
}

.animate-text-shine {
    background: linear-gradient(90deg, #ffffff 0%, #fbbf24 50%, #ffffff 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 3s linear infinite;
}

/* Payment Button Styles */
.presale-payment-button {
    background: rgba(220, 38, 38, 0.15);
    border-color: rgba(220, 38, 38, 0.3);
    color: white;
}

.presale-payment-button:hover {
    background: rgba(234, 179, 8, 0.2);
    border-color: rgba(234, 179, 8, 0.5);
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 4px 12px rgba(234, 179, 8, 0.3);
}

.presale-payment-active-eth {
    background: linear-gradient(135deg, rgba(98, 126, 234, 0.4), rgba(98, 126, 234, 0.2));
    border-color: rgba(98, 126, 234, 0.7);
    box-shadow: 0 0 20px rgba(98, 126, 234, 0.5);
    transform: scale(1.05);
}

.presale-payment-active-usdt {
    background: linear-gradient(135deg, rgba(38, 161, 123, 0.4), rgba(38, 161, 123, 0.2));
    border-color: rgba(38, 161, 123, 0.7);
    box-shadow: 0 0 20px rgba(38, 161, 123, 0.5);
    transform: scale(1.05);
}

.presale-payment-active-card {
    background: linear-gradient(135deg, rgba(234, 179, 8, 0.4), rgba(220, 38, 38, 0.2));
    border-color: rgba(234, 179, 8, 0.7);
    box-shadow: 0 0 20px rgba(234, 179, 8, 0.5);
    transform: scale(1.05);
}

/* Buy Button Styles */
.presale-buy-button {
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(220, 38, 38, 0.4);
    position: relative;
    overflow: hidden;
}

.presale-buy-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.presale-buy-button:hover::before {
    left: 100%;
}

.presale-buy-button:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 12px 32px rgba(220, 38, 38, 0.6);
    border-color: rgba(234, 179, 8, 0.6);
}

.presale-buy-button:active {
    transform: translateY(-1px) scale(1.01);
}

/* Price tag responsive adjustments */
.price-tag {
    word-break: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

.price-tag span {
    word-break: break-word;
    overflow-wrap: break-word;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .presale-container {
        padding: 1.25rem;
    }
    
    .price-tag {
        font-size: 0.9em;
    }
}

@media (max-width: 640px) {
    .presale-container {
        padding: 1rem;
    }
    
    .presale-card {
        padding: 0.75rem;
    }
    
    .price-tag {
        font-size: 0.85em;
    }
    
    .price-tag .text-2xl {
        font-size: 1.5rem !important;
    }
    
    .price-tag .text-xl {
        font-size: 1.25rem !important;
    }
}
</style>
