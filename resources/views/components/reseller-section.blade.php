<section id="reseller" class="py-20 bg-black text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 animate-fadeInUp">
            <h2 class="text-4xl md:text-5xl font-montserrat font-bold mb-6">
                <span class="text-accent">Reseller</span> Program
            </h2>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                Join our exclusive reseller program and earn profits by selling RWAMP tokens
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
            <div class="bg-gray-900 rounded-2xl p-8 animate-fadeInUp" x-data="resellerForm">
                <h3 class="text-2xl font-montserrat font-bold text-center mb-6">
                    Join the Reseller Program
                </h3>
                
                <form @submit.prevent="submitForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Full Name
                        </label>
                        <input
                            type="text"
                            x-model="formData.name"
                            required
                            class="form-input-dark"
                            placeholder="Enter your full name"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Phone Number
                        </label>
                        <input
                            type="tel"
                            x-model="formData.phone"
                            required
                            class="form-input-dark"
                            placeholder="+92 300 1234567"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Email Address
                        </label>
                        <input
                            type="email"
                            x-model="formData.email"
                            required
                            class="form-input-dark"
                            placeholder="your.email@example.com"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Company Name
                        </label>
                        <input
                            type="text"
                            x-model="formData.company"
                            class="form-input-dark"
                            placeholder="Your company name (optional)"
                        />
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Investment Capacity
                        </label>
                        <select
                            x-model="formData.investmentCapacity"
                            required
                            class="form-input-dark"
                        >
                            <option value="">Select your investment capacity</option>
                            <option value="1-10k">Rs 1,000 - Rs 10,000</option>
                            <option value="10-50k">Rs 10,000 - Rs 50,000</option>
                            <option value="50-100k">Rs 50,000 - Rs 100,000</option>
                            <option value="100k+">Rs 100,000+</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Message
                        </label>
                        <textarea
                            x-model="formData.message"
                            rows="3"
                            class="form-input-dark"
                            placeholder="Tell us about your experience and goals..."
                        ></textarea>
                    </div>
                    
                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-accent text-black py-4 rounded-lg font-montserrat font-bold text-lg hover:bg-yellow-400 transition-all duration-300 hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!loading">Apply Now</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </form>
                
                <!-- Success/Error Messages -->
                <div x-show="success" class="mt-4 p-4 bg-green-600 text-white rounded-lg text-center">
                    Thank you for your application! We'll contact you within 24 hours.
                </div>
                
                <div x-show="error" class="mt-4 p-4 bg-red-600 text-white rounded-lg text-center">
                    Something went wrong. Please try again later.
                </div>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-400">
                        We'll contact you within 24 hours to discuss your reseller application
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
