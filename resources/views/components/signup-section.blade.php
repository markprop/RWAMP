<section id="signup" class="py-20 bg-gradient-to-br from-primary to-red-600">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center text-white animate-on-scroll" data-animation="fadeInDown">
            <h2 class="text-4xl md:text-5xl font-montserrat font-bold mb-6">
                Be the First to Know
            </h2>
            <p class="text-xl md:text-2xl mb-12 opacity-90">
                Get Exclusive Updates about $RWAMP, platform updates and upcoming AirDrop campaigns.
            </p>
            
            <div class="newsletter-form bg-white/10 backdrop-blur-sm rounded-2xl p-8 md:p-12 animate-on-scroll" data-animation="zoomIn" x-data="newsletterForm" x-cloak>
                <div x-show="!success" x-cloak>
                    <form @submit.prevent="submitForm" class="space-y-6" data-phone-managed="alpine">
                        <!-- Honeypot field to trap bots -->
                        <div class="hidden" aria-hidden="true">
                            <label class="block text-sm">Leave this field empty</label>
                            <input type="text" x-model="hp" tabindex="-1" autocomplete="off" class="w-full px-4 py-2 rounded-md border border-white/30 bg-white/10" />
                        </div>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-white mb-2 text-left">
                                    Email Address
                                </label>
                                <input
                                    type="email"
                                    x-model="email"
                                    required
                                    class="w-full px-4 py-4 rounded-lg border border-white/30 bg-white/10 text-white placeholder-white/70 focus:border-white focus:ring-2 focus:ring-white/20 focus:outline-none transition-all duration-300"
                                    placeholder="your.email@example.com"
                                />
                                <p x-show="emailStatus === 'invalid'" x-cloak class="text-xs text-red-400 mt-1" x-text="emailMessage"></p>
                                <p x-show="emailStatus === 'valid'" x-cloak class="text-xs text-green-400 mt-1" x-text="emailMessage"></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white mb-2 text-left">
                                    WhatsApp Number
                                </label>
                                <div class="phone-input-wrapper relative"
                                     :class="{
                                        'ring-2 ring-green-500/30 border-green-500 rounded-lg': phoneStatus === 'valid',
                                        'ring-2 ring-yellow-500/30 border-yellow-500 rounded-lg': phoneStatus === 'incomplete',
                                        'ring-2 ring-red-500/30 border-red-500 rounded-lg': phoneStatus === 'invalid'
                                     }">
                                    <x-phone-input
                                        name="whatsapp"
                                        :required="true"
                                        placeholder="Enter phone number"
                                        input-class="w-full px-4 py-4 rounded-lg border border-white/30 bg-white/10 text-white placeholder-white/70 focus:border-white focus:ring-2 focus:ring-white/20 focus:outline-none transition-all duration-300"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                        <svg x-show="phoneStatus === 'valid'" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <svg x-show="phoneStatus === 'invalid'" class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="text-xs text-white/70 mt-1">Example: +92 300 1234567.</p>
                                <p x-show="phoneStatus === 'incomplete'" x-cloak class="text-xs text-yellow-400 mt-1" x-text="phoneMessage"></p>
                                <p x-show="phoneStatus === 'invalid'" x-cloak class="text-xs text-red-400 mt-1" x-text="phoneMessage"></p>
                                <p x-show="phoneStatus === 'valid' && phoneMessage" x-cloak class="text-xs text-green-400 mt-1" x-text="phoneMessage"></p>
                            </div>
                        </div>
                        
                        <button
                            type="submit"
                            :disabled="loading"
                            class="w-full md:w-auto bg-accent text-black py-4 px-12 rounded-lg font-montserrat font-bold text-lg hover:bg-yellow-400 transition-all duration-300 hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        aria-live="polite">
                            <span x-show="!loading">Subscribe Now</span>
                            <span x-show="loading" x-cloak class="flex items-center justify-center space-x-2">
                                <div class="w-5 h-5 border-2 border-black border-t-transparent rounded-full animate-spin"></div>
                                <span>Subscribing...</span>
                            </span>
                        </button>
                    </form>
                </div>
                
                <!-- Success Message -->
                <div x-show="success" x-cloak class="text-center animate-fadeInUp" style="display: none;">
                    <div class="w-20 h-20 bg-success rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-4xl text-white">✓</span>
                    </div>
                    <h3 class="text-2xl font-montserrat font-bold mb-4">
                        Thank You for Subscribing!
                    </h3>
                    <p class="text-lg opacity-90">
                        You'll receive exclusive updates about RWAMP launch and investment opportunities.
                    </p>
                </div>
                
                <!-- Error Message -->
                <div x-show="error" x-cloak class="text-center p-4 bg-red-600/20 rounded-lg" style="display: none;">
                    <p class="text-white" x-text="errorMessage || 'Something went wrong. Please try again later.'"></p>
                </div>
                
                <div class="mt-8 pt-6 border-t border-white/20">
                    <div class="grid md:grid-cols-3 gap-6 text-center" data-stagger data-animation="fadeInUp">
                        <div>
                            <div class="text-3xl mb-2">📧</div>
                            <h4 class="font-montserrat font-bold text-lg mb-2">Email Updates</h4>
                            <p class="text-sm opacity-80">Weekly investment insights and project updates</p>
                        </div>
                        <div>
                            <div class="text-3xl mb-2">📱</div>
                            <h4 class="font-montserrat font-bold text-lg mb-2">WhatsApp Alerts</h4>
                            <p class="text-sm opacity-80">Instant notifications about presale opportunities</p>
                        </div>
                        <div>
                            <div class="text-3xl mb-2">🎯</div>
                            <h4 class="font-montserrat font-bold text-lg mb-2">Exclusive Access</h4>
                            <p class="text-sm opacity-80">Early access to new projects and special offers</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Trust indicators -->
            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-8 opacity-80" data-stagger data-animation="fadeInUp">
                <div class="text-center" data-animate-counter>
                    <div class="text-2xl font-mono font-bold" data-counter>1000+</div>
                    <div class="text-sm">Early Subscribers</div>
                </div>
                <div class="text-center" data-animate-counter>
                    <div class="text-2xl font-mono font-bold" data-counter>3</div>
                    <div class="text-sm">Countries</div>
                </div>
                <div class="text-center" data-animate-counter>
                    <div class="text-2xl font-mono font-bold" data-counter>50+</div>
                    <div class="text-sm">Projects Planned</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-mono font-bold">24/7</div>
                    <div class="text-sm">Support</div>
                </div>
            </div>
        </div>
    </div>
</section>
