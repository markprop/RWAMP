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
                        <div class="mt-2 text-accent font-mono font-bold">40% to 60% Discount</div>
                        <p class="text-xs text-gray-400 mt-1">Special reseller pricing available</p>
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
                        <p class="text-gray-300">Set your own price (or you can use official price)</p>
                        <div class="mt-2 text-accent font-mono font-bold">Your Selling Price</div>
                        <p class="text-xs text-gray-400 mt-1">Flexible pricing strategy</p>
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
                        <div class="mt-2 text-success font-mono font-bold">Calculated automatically</div>
                        <p class="text-xs text-gray-400 mt-1">See calculator below</p>
                    </div>
                </div>
                
                <!-- Highlight Box - Dynamic Profit Calculator -->
                <div class="bg-gradient-to-r from-accent to-yellow-500 rounded-lg p-6 text-black" x-data="profitCalculator">
                    <div class="text-center">
                        <h4 class="font-montserrat font-bold text-xl mb-4">Profit Calculator</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">Buy at:</span>
                                <input 
                                    type="number" 
                                    x-model="buyPrice" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="0.00"
                                    class="w-24 px-2 py-1 text-right font-mono font-bold bg-white/90 border border-black/20 rounded text-black"
                                />
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">Sell at:</span>
                                <input 
                                    type="number" 
                                    x-model="sellPrice" 
                                    step="0.01" 
                                    min="0"
                                    placeholder="0.00"
                                    class="w-24 px-2 py-1 text-right font-mono font-bold bg-white/90 border border-black/20 rounded text-black"
                                />
                            </div>
                            <div class="border-t-2 border-black pt-3 flex justify-between items-center font-bold text-lg">
                                <span>Profit per token:</span>
                                <span class="font-mono" x-text="'Rs ' + profitPerToken">Rs 0.00</span>
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
                
                <form @submit.prevent="submitForm" class="space-y-6" data-reseller-form>
                    <!-- Honeypot to deter bots -->
                    <div class="hidden" aria-hidden="true">
                        <label class="block text-sm">Do not fill this field</label>
                        <input type="text" x-model="formData.hp" tabindex="-1" autocomplete="off" class="form-input" />
                    </div>
                    <input type="hidden" x-ref="recaptcha" />
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                            Full Name <span class="text-red-400">*</span>
                            <div class="relative group">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('name', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'name'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Full Name:</strong> Enter your complete legal name as it appears on official documents. Format: First Name Last Name (e.g., John Doe or Muhammad Ali)</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="formData.name"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                                placeholder="Enter your complete legal name"
                                x-on:input="validateName($event.target.value)"
                                x-on:blur="validateName($event.target.value)"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="nameValidated && nameStatus === 'valid' && formData.name.trim().length > 0" class="text-green-500">✓</span>
                                <span x-show="nameValidated && nameStatus === 'invalid' && formData.name.trim().length > 0" class="text-red-500">✗</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Format: First Name Last Name (e.g., John Doe)</p>
                        <p x-show="nameValidated && nameStatus === 'invalid' && formData.name.trim().length > 0" x-cloak class="text-xs text-red-400 mt-1" x-text="nameMessage"></p>
                        <p x-show="nameValidated && nameStatus === 'valid' && formData.name.trim().length > 0" x-cloak class="text-xs text-green-400 mt-1" x-text="nameMessage"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                            Phone Number <span class="text-red-400">*</span>
                            <div class="relative group">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('phone', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'phone'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Phone Number:</strong> Enter your phone number with country code. The system will auto-format it to Pakistan format (+92) if applicable. Examples: +92 300 1234567, 03001234567, 3001234567</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input
                                type="tel"
                                x-model="formData.phone"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                                placeholder="+92 300 1234567"
                                x-on:input="formatPhone($event.target)"
                                x-on:blur="validatePhone($event.target.value)"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="phoneStatus === 'checking' && formData.phone.trim().length > 0" class="animate-spin text-gray-400">⟳</span>
                                <span x-show="phoneValidated && phoneStatus === 'valid' && formData.phone.trim().length > 0 && phoneStatus !== 'checking'" class="text-green-500">✓</span>
                                <span x-show="phoneValidated && phoneStatus === 'invalid' && formData.phone.trim().length > 0 && phoneStatus !== 'checking'" class="text-red-500">✗</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Auto-formats to Pakistan code (+92). Accepts any format.</p>
                        <p x-show="phoneValidated && phoneStatus === 'invalid' && formData.phone.trim().length > 0 && phoneStatus !== 'checking'" x-cloak class="text-xs text-red-400 mt-1" x-text="phoneMessage"></p>
                        <p x-show="phoneValidated && phoneStatus === 'valid' && formData.phone.trim().length > 0 && phoneStatus !== 'checking'" x-cloak class="text-xs text-green-400 mt-1" x-text="phoneMessage"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                            Email Address <span class="text-red-400">*</span>
                            <div class="relative group">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('email', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'email'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Email Address:</strong> Enter a valid email address. The system will verify that the email exists and is available. Format: user@example.com. You'll receive application notifications at this email.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input
                                type="email"
                                x-model="formData.email"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                                placeholder="your.email@example.com"
                                x-on:input.debounce.500ms="validateEmail($event.target.value)"
                                x-on:blur="validateEmail($event.target.value)"
                            />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="emailStatus === 'checking' && formData.email.trim().length > 0" class="animate-spin text-gray-400">⟳</span>
                                <span x-show="emailValidated && emailStatus === 'valid' && formData.email.trim().length > 0 && emailStatus !== 'checking'" class="text-green-500">✓</span>
                                <span x-show="emailValidated && emailStatus === 'invalid' && formData.email.trim().length > 0 && emailStatus !== 'checking'" class="text-red-500">✗</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Format: valid email address (e.g., user@example.com)</p>
                        <p x-show="emailValidated && emailStatus === 'invalid' && formData.email.trim().length > 0 && emailStatus !== 'checking'" x-cloak class="text-xs text-red-400 mt-1" x-text="emailMessage"></p>
                        <p x-show="emailValidated && emailStatus === 'valid' && formData.email.trim().length > 0 && emailStatus !== 'checking'" x-cloak class="text-xs text-green-400 mt-1" x-text="emailMessage"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                            Company Name <span class="text-gray-400 text-xs">(Optional)</span>
                            <div class="relative group">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('company', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'company'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Company Name:</strong> Enter your business or company name if you're applying as a business entity. This field is optional - leave blank if you're an individual.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
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
                        <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                            Investment Capacity <span class="text-red-400">*</span>
                            <div class="relative group">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('investment', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'investment'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Investment Capacity:</strong> Select the range that matches your investment budget. This helps us understand your business scale and provide appropriate support.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
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
                        <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">
                            Message <span class="text-gray-400 text-xs">(Optional)</span>
                            <div class="relative group">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('message', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'message'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Message:</strong> Tell us about your experience, goals, and why you want to become a partner. This field is optional but recommended as it helps with your application review.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
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
                document.addEventListener('DOMContentLoaded', () => {
                    // Wait for Alpine to initialize, then find the resellerForm component
                    setTimeout(() => {
                        const container = document.querySelector('[x-data="resellerForm"]');
                        if (container && container.__x && container.__x.$data) {
                            container.__x.$data.getRecaptcha = function(action){
                                return new Promise((resolve) => {
                                    if (window.grecaptcha) {
                                        grecaptcha.ready(function(){
                                            grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action}).then(resolve);
                                        });
                                    } else {
                                        resolve('');
                                    }
                                });
                            };
                        }
                    }, 100);
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
