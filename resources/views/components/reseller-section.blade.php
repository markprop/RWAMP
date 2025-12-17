@props([

    'headline' => 'Join the Partner Program',

])



<section id="become-partner" class="relative py-20 bg-gradient-to-b from-gray-950 via-gray-900 to-black overflow-hidden">

    <div class="absolute inset-0 pointer-events-none">

        <div class="absolute -top-40 -right-40 w-80 h-80 bg-accent/10 rounded-full blur-3xl"></div>

        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>

    </div>



    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        <div class="grid lg:grid-cols-2 gap-12 items-start">

            <!-- Left content -->

            <div class="space-y-8">

                <div>

                    <p class="inline-flex items-center text-sm font-semibold uppercase tracking-[0.2em] text-accent/80 mb-3">

                        <span class="w-8 h-[1px] bg-accent mr-2"></span>

                        Become a Partner

                    </p>

                    <h2 class="text-3xl sm:text-4xl font-montserrat font-bold text-white mb-4">

                        {{ $headline }}

                    </h2>

                    <p class="text-gray-300 text-base leading-relaxed max-w-xl">

                        Partner with RWAMP to bring tokenized real estate investments to your clients and network. Enjoy recurring commissions, dedicated support, and early access to new global property offerings.

                    </p>

                </div>



                <div class="space-y-4">

                    <div class="flex gap-3">

                        <div class="mt-1">

                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent/10 text-accent">

                                ✓

                            </span>

                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-white mb-1">High-Value Referral Rewards</h3>

                            <p class="text-sm text-gray-400">Earn attractive commissions on every successful investment made through your referrals, with transparent tracking and on-time payouts.</p>

                        </div>

                    </div>



                    <div class="flex gap-3">

                        <div class="mt-1">

                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent/10 text-accent">

                                ✓

                            </span>

                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-white mb-1">Dedicated Partner Support</h3>

                            <p class="text-sm text-gray-400">Get marketing kits, training material, and one-on-one assistance from RWAMP’s partnership team to help you close more deals.</p>

                        </div>

                    </div>



                    <div class="flex gap-3">

                        <div class="mt-1">

                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-accent/10 text-accent">

                                ✓

                            </span>

                        </div>

                        <div>

                            <h3 class="text-sm font-semibold text-white mb-1">Access to Global Real Estate Deals</h3>

                            <p class="text-sm text-gray-400">Offer your network carefully vetted, income-generating properties in Dubai, Pakistan, Saudi Arabia, and beyond through RWAMP tokens.</p>

                        </div>

                    </div>

                </div>



                <div class="pt-4 border-t border-white/5 mt-6">

                    <p class="text-xs text-gray-500 flex items-center gap-2">

                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-800 text-[10px]">i</span>

                        RWAMP works only with compliant, KYC-verified partners. Your application will be reviewed by our team before approval.

                    </p>

                </div>

            </div>



            <!-- Right form -->

            <div class="become-partner-form bg-gray-900/80 backdrop-blur-xl border border-white/10 rounded-2xl shadow-2xl p-6 sm:p-8 relative overflow-visible" x-data="resellerForm()">

                <div class="absolute inset-0 pointer-events-none opacity-40" aria-hidden="true">

                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-accent/10 rounded-full blur-3xl"></div>

                    <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl"></div>

                </div>



                <div class="relative z-10">

                    <div class="flex items-center justify-between mb-6">

                        <div>

                            <p class="text-xs font-semibold text-accent/80 mb-1 uppercase tracking-[0.18em]">Partner Application</p>

                            <h3 class="text-xl sm:text-2xl font-montserrat font-bold text-white">Join the Partner Program</h3>

                            <p class="text-xs text-gray-400 mt-1">Fill out the form below to apply for our partner program</p>

                        </div>

                        <div class="hidden sm:flex items-center gap-2 text-xs text-gray-400">

                            <span class="h-8 w-8 rounded-full bg-gray-800 flex items-center justify-center border border-white/10">

                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14M3 7l9-4 9 4M3 7l9 4 9-4M12 11v10" />

                                </svg>

                            </span>

                            <span class="max-w-[120px] leading-tight">Secure, encrypted application</span>

                        </div>

                    </div>



                    <!-- Alerts -->

                    <div x-show="success" x-cloak class="mb-4 rounded-lg border border-green-500/40 bg-green-500/10 px-4 py-3 text-xs text-green-200 flex items-start gap-2">

                        <span class="mt-0.5">✓</span>

                        <p x-text="successMessage || 'Thank you! Your application has been submitted successfully. Our team will contact you soon.'"></p>

                    </div>

                    <div x-show="error" x-cloak class="mb-4 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-xs text-red-200 flex items-start gap-2">

                        <span class="mt-0.5">!</span>

                        <p x-text="errorMessage || 'Something went wrong. Please check your details and try again.'"></p>

                    </div>



                    <form x-on:submit.prevent="submitForm" class="space-y-5" novalidate>

                        <div class="grid md:grid-cols-2 gap-5">

                            <div>

                                <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">

                                    Full Name <span class="text-red-400">*</span>

                                    <div class="relative group">

                                        <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('name', $event)">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                                        </svg>

                                        <div x-show="tooltip === 'name'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">

                                            <p><strong>Full Name:</strong> Enter your official full name as per your government-issued ID or passport.</p>

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

                                            <p><strong>Phone Number:</strong> Select your country and enter your full mobile number (e.g., +92 300 1234567).</p>

                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">

                                                <div class="border-4 border-transparent border-t-gray-900"></div>

                                            </div>

                                        </div>

                                    </div>

                                </label>

                                <div class="phone-input-wrapper w-full"
                                     :class="{
                                        'ring-2 ring-green-500/30 border-green-500': phoneStatus === 'valid',
                                        'ring-2 ring-yellow-500/30 border-yellow-500': phoneStatus === 'incomplete',
                                        'ring-2 ring-red-500/30 border-red-500': phoneStatus === 'invalid'
                                     }">
                                    <x-phone-input
                                        name="phone"
                                        id="phone"
                                        :required="true"
                                        placeholder="Enter phone number"
                                        input-class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"
                                    />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg x-show="phoneStatus === 'valid'" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <svg x-show="phoneStatus === 'invalid'" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>

                                <p class="text-xs text-gray-400 mt-1">Example: +92 300 1234567.</p>
                                <p x-show="phoneStatus === 'incomplete'" x-cloak class="text-xs text-yellow-400 mt-1" x-text="phoneMessage"></p>
                                <p x-show="phoneStatus === 'invalid'" x-cloak class="text-xs text-red-400 mt-1" x-text="phoneMessage"></p>
                                <p x-show="phoneStatus === 'valid' && phoneMessage" x-cloak class="text-xs text-green-400 mt-1" x-text="phoneMessage"></p>

                            </div>

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

                                    x-on:input="debouncedValidateEmail($event.target.value)"

                                    x-on:blur="validateEmail($event.target.value)"

                                />

                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">

                                    <span x-show="emailValidated && emailStatus === 'valid'" class="text-green-500">✓</span>

                                    <span x-show="emailValidated && emailStatus === 'invalid'" class="text-red-500">✗</span>

                                </div>

                            </div>

                            <p class="text-xs text-gray-400 mt-1">Format: valid email address (e.g., user@example.com)</p>

                            <p x-show="emailValidated && emailStatus === 'invalid'" x-cloak class="text-xs text-red-400 mt-1" x-text="emailMessage"></p>

                            <p x-show="emailValidated && emailStatus === 'valid'" x-cloak class="text-xs text-green-400 mt-1" x-text="emailMessage"></p>

                        </div>



                        <div class="grid md:grid-cols-2 gap-5">

                            <div>

                                <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">

                                    Company Name <span class="text-gray-400 text-xs font-normal">(Optional)</span>

                                    <div class="relative group">

                                        <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('company', $event)">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14M3 7l9-4 9 4M3 7l9 4 9-4M12 11v10" />

                                        </svg>

                                        <div x-show="tooltip === 'company'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">

                                            <p><strong>Company Name:</strong> If you are applying as a business, enter your registered company name as it appears on official documents.</p>

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

                                <p class="text-xs text-gray-500 mt-1">Leave blank if you're an individual investor or consultant.</p>

                            </div>



                            <div>

                                <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">

                                    Investment Capacity <span class="text-red-400">*</span>

                                    <div class="relative group">

                                        <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('capacity', $event)">

                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                                        </svg>

                                        <div x-show="tooltip === 'capacity'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">

                                            <p><strong>Investment Capacity:</strong> Select the approximate investment volume you can bring to RWAMP within the next 6–12 months.</p>

                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">

                                                <div class="border-4 border-transparent border-t-gray-900"></div>

                                            </div>

                                        </div>

                                    </div>

                                </label>

                                <select

                                    x-model="formData.investmentCapacity"

                                    required

                                    class="w-full px-4 py-3 rounded-lg border border-gray-600 bg-gray-800 text-white placeholder-gray-400 focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none transition-all duration-300"

                                >

                                    <option value="" disabled selected>Select estimated investment volume</option>

                                    <option value="1-10k">USD 1,000 – 10,000</option>

                                    <option value="10-50k">USD 10,000 – 50,000</option>

                                    <option value="50-100k">USD 50,000 – 100,000</option>

                                    <option value="100k+">Above USD 100,000</option>

                                </select>

                                <p class="text-xs text-gray-400 mt-1">This helps us understand the level of support and opportunities to offer you.</p>

                            </div>

                        </div>



                        <div>

                            <label class="block text-sm font-semibold text-white mb-2 flex items-center gap-2">

                                How did you hear about RWAMP? <span class="text-gray-400 text-xs font-normal">(Optional)</span>

                                <div class="relative group">

                                    <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop="showTooltip('source', $event)">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>

                                    </svg>

                                    <div x-show="tooltip === 'source'" @click.away="tooltip = null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">

                                        <p><strong>How did you hear about us?</strong> This helps us understand which channels are most effective in reaching partners like you.</p>

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

                                placeholder="Share details about your existing client base, region, and how you plan to promote RWAMP."

                            ></textarea>

                            <p class="text-xs text-gray-500 mt-1">Optional: Tell us more about your background and how you plan to collaborate with RWAMP.</p>

                        </div>



                        <!-- Honeypot field (hidden from users) -->

                        <div class="hidden">

                            <label>Leave this field empty</label>

                            <input type="text" x-model="formData.hp" autocomplete="off">

                        </div>



                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">

                            <div class="text-[11px] text-gray-500 flex items-center gap-2">

                                <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-800 text-[10px]">i</span>

                                <span>By submitting, you agree that RWAMP may contact you regarding partnership opportunities.</span>

                            </div>

                            <button

                                type="submit"

                                :disabled="loading"

                                class="inline-flex items-center justify-center px-8 py-3 rounded-lg bg-accent text-black text-sm font-semibold hover:bg-yellow-400 transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed shadow-lg shadow-accent/20"

                            >

                                <span x-show="!loading">Submit Application</span>

                                <span x-show="loading" class="flex items-center gap-2">

                                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>

                                        <path class="opacity-75" stroke-width="4" d="M4 12a8 8 0 018-8"></path>

                                    </svg>

                                    Processing...

                                </span>

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

