<section class="relative min-h-screen flex items-center justify-center bg-dubai-skyline bg-cover bg-center bg-no-repeat">
    <!-- Dark overlay -->
    <div class="absolute inset-0 bg-black/60"></div>
    
    <!-- Content -->
    <div class="relative z-10 text-center text-white px-4 max-w-6xl mx-auto">
        <div class="animate-fadeInUp">
            <!-- Logo -->
            <div class="mb-8 flex justify-center">
                <div class="w-24 h-24 rounded-full overflow-hidden shadow-2xl">
                    <img 
                        src="{{ asset('images/logo.jpeg') }}" 
                        alt="RWAMP Logo" 
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-montserrat font-bold mb-6">
                RWAMP is Coming Soon 
                <span class="text-6xl">🚀</span>
            </h1>
            
            <p class="text-xl md:text-2xl mb-8 text-gray-200 max-w-3xl mx-auto">
                The Currency of Real Estate Investments
            </p>
            
            <!-- Countdown Timer -->
            <div class="mb-12" x-data="countdown">
                <div x-show="!completed" class="countdown-container">
                    <div class="countdown-item">
                        <div class="countdown-number" x-text="timeLeft.days"></div>
                        <div class="countdown-label">Days</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" x-text="timeLeft.hours"></div>
                        <div class="countdown-label">Hours</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" x-text="timeLeft.minutes"></div>
                        <div class="countdown-label">Minutes</div>
                    </div>
                    <div class="countdown-item">
                        <div class="countdown-number" x-text="timeLeft.seconds"></div>
                        <div class="countdown-label">Seconds</div>
                    </div>
                </div>
                <div x-show="completed" class="text-4xl font-mono text-accent">
                    Launch Day!
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <button
                    @click="$store.modal.open = true"
                    class="btn-primary w-full sm:w-auto transition-all duration-300 hover:scale-105"
                >
                    Join the Presale
                </button>
                
                <button
                    x-data="smoothScroll"
                    @click="scrollTo('reseller')"
                    class="btn-secondary w-full sm:w-auto transition-all duration-300 hover:scale-105"
                >
                    Become a Reseller
                </button>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
    </div>

    @include('components.presale-modal')
</section>
