<section id="reseller" class="py-20 bg-black text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 animate-fadeInUp">
            <h2 class="text-4xl md:text-5xl font-montserrat font-bold mb-6">
                <span class="text-accent">Partner</span> Program
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Join our exclusive partner program and earn profits by selling RWAMP tokens
            </p>
        </div>
        
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left side - Infographic -->
            <div class="space-y-8 animate-fadeInUp">
                <div class="text-center">
                    <h3 class="text-2xl font-montserrat font-bold text-accent mb-8">
                        How It Works
                    </h3>
                </div>
                
                <!-- Step 1 -->
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-black font-bold text-lg">1</span>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-6 flex-1">
                        <h4 class="font-montserrat font-bold text-lg mb-2">Bulk Buy</h4>
                        <p class="text-gray-300">Purchase RWAMP tokens at wholesale prices</p>
                        <div class="mt-2 text-accent font-mono font-bold">Rs 0.70 per token</div>
                    </div>
                </div>
                
                <!-- Arrow -->
                <div class="flex justify-center">
                    <div class="w-0.5 h-8 bg-accent"></div>
                </div>
                
                <!-- Step 2 -->
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-black font-bold text-lg">2</span>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-6 flex-1">
                        <h4 class="font-montserrat font-bold text-lg mb-2">Resell</h4>
                        <p class="text-gray-300">Sell tokens to your network at market price</p>
                        <div class="mt-2 text-accent font-mono font-bold">Rs 0.90 per token</div>
                    </div>
                </div>
                
                <!-- Arrow -->
                <div class="flex justify-center">
                    <div class="w-0.5 h-8 bg-accent"></div>
                </div>
                
                <!-- Step 3 -->
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-accent rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-black font-bold text-lg">3</span>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-6 flex-1">
                        <h4 class="font-montserrat font-bold text-lg mb-2">Profit</h4>
                        <p class="text-gray-300">Keep the difference as your profit</p>
                        <div class="mt-2 text-success font-mono font-bold">Rs 0.20 per token</div>
                    </div>
                </div>
                
                <!-- Highlight Box -->
                <div class="bg-gradient-to-r from-accent to-yellow-500 rounded-lg p-6 text-black">
                    <div class="text-center">
                        <h4 class="font-montserrat font-bold text-xl mb-2">Profit Calculator</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Buy at:</span>
                                <span class="font-mono font-bold">Rs 0.70</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Sell at:</span>
                                <span class="font-mono font-bold">Rs 0.90</span>
                            </div>
                            <div class="border-t border-black pt-2 flex justify-between font-bold text-lg">
                                <span>Profit per token:</span>
                                <span class="font-mono">Rs 0.20</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Form -->
            <div class="bg-gray-900 rounded-2xl p-8 animate-fadeInUp" x-data="resellerForm" x-cloak>
                <h3 class="text-2xl font-montserrat font-bold text-center mb-2 text-white">
                    Join the Partner Program
                </h3>
                <p class="text-center text-gray-400 text-sm mb-6">
                    Fill out the form below to apply for our partner program
                </p>

                <!-- Form Guidelines -->
                <div class="mb-6 p-4 bg-blue-900/30 border border-blue-500/30 rounded-lg">
                    <h4 class="text-white font-bold text-sm mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        How to Fill This Form
                    </h4>
                    <ul class="text-xs text-gray-300 space-y-1.5 list-disc list-inside">
                        <li><span class="font-semibold text-white">Full Name:</span> Enter your complete legal name as it appears on official documents</li>
                        <li><span class="font-semibold text-white">Phone Number:</span> Include country code (e.g., +92 300 1234567)</li>
                        <li><span class="font-semibold text-white">Email Address:</span> Use a valid email you check regularly</li>
                        <li><span class="font-semibold text-white">Company Name:</span> Optional - leave blank if you're an individual</li>
                        <li><span class="font-semibold text-white">Investment Capacity:</span> Select the range that matches your investment budget</li>
                        <li><span class="font-semibold text-white">Message:</span> Tell us about your experience and goals (optional but recommended)</li>
                    </ul>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-600 text-white rounded-lg text-center">
                        {{ session('success') }} Your request is pending admin approval.
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-600 text-white rounded-lg text-center">
                        {{ $errors->first() }}
                    </div>
                @endif
                
                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Honeypot to deter bots -->
                    <div class="hidden" aria-hidden="true">
                        <label class="block text-sm">Do not fill this field</label>
                        <input type="text" x-model="formData.hp" tabindex="-1" autocomplete="off" class="form-input" />
                    </div>
                    <input type="hidden" x-ref="recaptcha" />
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">
                            Full Name <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="text"
                            x-model="formData.name"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                            placeholder="Enter your complete legal name"
                        />
                        <p class="text-xs text-gray-400 mt-1">As it appears on official documents</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">
                            Phone Number <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="tel"
                            x-model="formData.phone"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                            placeholder="+92 300 1234567"
                        />
                        <p class="text-xs text-gray-400 mt-1">Include country code (e.g., +92 for Pakistan)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">
                            Email Address <span class="text-red-400">*</span>
                        </label>
                        <input
                            type="email"
                            x-model="formData.email"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                            placeholder="your.email@example.com"
                        />
                        <p class="text-xs text-gray-400 mt-1">We'll send updates to this email</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">
                            Company Name <span class="text-gray-400 text-xs">(Optional)</span>
                        </label>
                        <input
                            type="text"
                            x-model="formData.company"
                            class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                            placeholder="Your company name (if applicable)"
                        />
                        <p class="text-xs text-gray-400 mt-1">Leave blank if you're an individual</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">
                            Investment Capacity <span class="text-red-400">*</span>
                        </label>
                        <select
                            x-model="formData.investmentCapacity"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                        >
                            <option value="" class="bg-gray-800 text-gray-400">Select your investment capacity</option>
                            <option value="1-10k" class="bg-gray-800 text-white">Rs 1,000 - Rs 10,000</option>
                            <option value="10-50k" class="bg-gray-800 text-white">Rs 10,000 - Rs 50,000</option>
                            <option value="50-100k" class="bg-gray-800 text-white">Rs 50,000 - Rs 100,000</option>
                            <option value="100k+" class="bg-gray-800 text-white">Rs 100,000+</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Select the range that matches your budget</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2">
                            Message <span class="text-gray-400 text-xs">(Optional)</span>
                        </label>
                        <textarea
                            x-model="formData.message"
                            rows="4"
                            class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300 resize-none"
                            placeholder="Tell us about your experience, goals, and why you want to become a partner..."
                        ></textarea>
                        <p class="text-xs text-gray-400 mt-1">Share your background and objectives (recommended)</p>
                    </div>
                    
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-accent text-black py-4 rounded-lg font-montserrat font-bold text-lg hover:bg-yellow-400 transition-all duration-300 hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-live="polite"
                    >
                        <span x-show="!loading">Apply Now</span>
                        <span x-show="loading" x-cloak>Processing...</span>
                    </button>
                </form>
                @if (config('services.recaptcha.site_key'))
                <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
                <script>
                document.addEventListener('alpine:init', () => {
                    const container = document.currentScript.closest('[x-data]');
                    container.__x && (container.__x.$data.getRecaptcha = function(action){
                        return new Promise((resolve) => {
                            grecaptcha.ready(function(){
                                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action}).then(resolve);
                            });
                        })
                    });
                });
                </script>
                @endif
                
                <!-- Success/Error Messages -->
                <div x-show="success" x-cloak class="mt-4 p-4 bg-green-600 text-white rounded-lg text-center" style="display: none;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Thank you for your application! We'll contact you within 24 hours.</span>
                    </div>
                </div>
                
                <div x-show="error" x-cloak class="mt-4 p-4 bg-red-600 text-white rounded-lg text-center" style="display: none;">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span x-text="getErrorMessage()"></span>
                    </div>
                </div>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-400">
                        We'll contact you within 24 hours to discuss your partner application
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
