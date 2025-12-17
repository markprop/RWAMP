<section class="relative min-h-screen flex items-center bg-dubai-skyline bg-cover bg-center bg-no-repeat">
    <!-- Dark overlay -->
    <div class="absolute inset-0 bg-black/60"></div>
    
    <!-- Content - Two Column Layout -->
    <div class="relative z-10 w-full px-4 py-12 max-w-7xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- Left Column: Hero Content -->
            <div class="text-center lg:text-left text-white">
                <!-- Logo -->
                <div class="mb-8 flex justify-center lg:justify-start animate-on-scroll" data-animation="zoomIn" style="animation-delay: 0.1s;">
                    <div class="w-32 h-32 md:w-40 md:h-40 lg:w-48 lg:h-48 rounded-full overflow-hidden shadow-2xl transition-transform duration-300 hover:scale-105 coin-ring">
                        <img 
                            src="{{ asset('images/logo.png') }}" 
                            alt="RWAMP Logo" 
                            class="w-full h-full object-cover rwamp-coin-logo"
                        />
                    </div>
                </div>
                
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-montserrat font-bold mb-6 animate-on-scroll" data-animation="fadeInUp" style="animation-delay: 0.2s;">
                    RWAMP is Coming Soon 
                    <span class="text-5xl md:text-6xl inline-block animate-on-scroll" data-animation="zoomIn" style="animation-delay: 0.4s;">🚀</span>
                </h1>
                
                <p class="text-lg md:text-xl lg:text-2xl mb-8 text-gray-200 max-w-2xl mx-auto lg:mx-0 animate-on-scroll" data-animation="fadeInUp" style="animation-delay: 0.3s;">
                    The Currency of Real Estate Investments
                </p>
                
                <!-- Countdown Timer -->
                <div class="mb-8 lg:mb-12 animate-on-scroll" data-animation="fadeInUp" style="animation-delay: 0.5s;" x-data="countdown">
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
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start items-center animate-on-scroll" data-animation="fadeInUp" style="animation-delay: 0.6s;" x-data="smoothScroll">
                    @auth
                        <a
                            href="{{ route('open.purchase') }}"
                            class="btn-primary w-full sm:w-auto transition-all duration-300 hover:scale-105 text-center"
                        >
                            Buy $RWAMP Now!
                        </a>
                    @else
                        <a
                            href="{{ route('login', ['intended' => 'purchase']) }}"
                            class="btn-primary w-full sm:w-auto transition-all duration-300 hover:scale-105 text-center"
                        >
                            Buy $RWAMP Now!
                        </a>
                    @endauth
                    
                    <a
                        href="{{ route('become.partner') }}"
                        class="btn-secondary w-full sm:w-auto transition-all duration-300 hover:scale-105 text-center"
                    >
                        Become a Partner
                    </a>
                </div>
            </div>
            
            <!-- Right Column: Presale Section -->
            <div class="lg:pl-8">
                @include('components.presale-section', ['presaleData' => $presaleData ?? []])
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
    </div>
</section>
