<section id="signup" class="py-20 bg-gradient-to-br from-primary to-red-600">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center text-white animate-on-scroll" data-animation="fadeInDown">
            <h2 class="text-4xl md:text-5xl font-montserrat font-bold mb-6">
                Be the First to Know
            </h2>
            <p class="text-xl md:text-2xl mb-12 opacity-90">
                Get exclusive updates about RWAMP launch, presale opportunities, and investment insights
            </p>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 md:p-12 animate-on-scroll" data-animation="zoomIn" x-data="newsletterForm" x-cloak>
                <div x-show="!success" x-cloak>
                    <form @submit.prevent="submitForm" class="space-y-6">
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
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white mb-2 text-left">
                                    WhatsApp Number
                                </label>
                                <input
                                    type="tel"
                                    x-model="whatsapp"
                                    required
                                    class="w-full px-4 py-4 rounded-lg border border-white/30 bg-white/10 text-white placeholder-white/70 focus:border-white focus:ring-2 focus:ring-white/20 focus:outline-none transition-all duration-300"
                                    placeholder="+92 300 1234567"
                                />
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
                    <p class="text-white">Something went wrong. Please try again later.</p>
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
