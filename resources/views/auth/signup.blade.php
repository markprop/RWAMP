@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-montserrat font-bold">Create your account</h1>
            <p class="text-white/80 mt-2 text-sm sm:text-base">Join as an Investor or a Reseller</p>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-start">
            <!-- Promotional Section -->
            <div class="hidden lg:block space-y-6 text-white sticky top-8">
                <div class="bg-gradient-to-br from-primary/20 to-secondary/20 backdrop-blur-lg rounded-2xl p-8 border border-primary/30 shadow-2xl">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="coin-ring">
                            <img src="{{ asset('images/logo.png') }}" alt="RWAMP" class="w-16 h-16 rounded-full rwamp-coin-logo">
                        </div>
                        <div>
                            <h2 class="text-3xl font-montserrat font-bold text-white">RWAMP Coin</h2>
                            <p class="text-white/80 text-sm">Start Your Investment Journey Today</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">💎</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">Premium Investment Platform</h3>
                                <p class="text-white/70 text-sm">Join thousands of successful investors who trust RWAMP for their cryptocurrency investments.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">📊</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">Expert Market Analysis</h3>
                                <p class="text-white/70 text-sm">Get access to professional market insights and make informed investment decisions.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">🔒</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">Bank-Level Security</h3>
                                <p class="text-white/70 text-sm">Your investments are protected with advanced encryption and multi-layer security protocols.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">⚡</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">Instant Transactions</h3>
                                <p class="text-white/70 text-sm">Buy, sell, and trade RWAMP coins instantly with minimal transaction fees.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/10 rounded-lg p-4 border border-white/20 mb-4">
                        <h4 class="font-semibold mb-3 flex items-center gap-2">
                            <span>🎯</span> Why Sign Up?
                        </h4>
                        <ul class="space-y-2 text-sm text-white/80">
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Start investing with as little as you want
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Access exclusive investment packages
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Earn passive income through smart contracts
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Join our referral program and earn rewards
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Get priority support from our expert team
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Participate in exclusive token sales
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gradient-to-r from-primary/30 to-secondary/30 rounded-lg p-4 border border-primary/40">
                        <div class="flex items-center gap-3">
                            <div class="text-4xl">🚀</div>
                            <div>
                                <h4 class="font-bold text-lg mb-1">Limited Time Offer</h4>
                                <p class="text-white/80 text-sm">Sign up now and get bonus coins on your first investment!</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center border border-white/20">
                        <div class="text-3xl mb-2">💵</div>
                        <div class="text-2xl font-bold text-primary">Low</div>
                        <div class="text-xs text-white/70">Fees</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center border border-white/20">
                        <div class="text-3xl mb-2">🌍</div>
                        <div class="text-2xl font-bold text-primary">Global</div>
                        <div class="text-xs text-white/70">Access</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center border border-white/20">
                        <div class="text-3xl mb-2">🏆</div>
                        <div class="text-2xl font-bold text-primary">Top</div>
                        <div class="text-xs text-white/70">Rated</div>
                    </div>
                </div>
            </div>
            
            <!-- Signup Form Section -->
            <div class="w-full" x-data="signupTabs">
                @if (session('success'))
                    <div class="mb-6 rounded-lg border border-green-300 bg-green-50 text-green-800 px-4 py-3 text-sm">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3 text-sm">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                
                @if(request('ref'))
                    <div class="mb-6 rounded-lg border border-blue-300 bg-blue-50 text-blue-800 px-4 py-3 text-sm">
                        <p><strong>Referral Link Detected!</strong> The referral code from the URL has been pre-filled. You can modify it if needed.</p>
                    </div>
                @endif

                <div class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6 md:p-8 animate-fadeInUp">
                    <div class="flex gap-2 mb-6 sm:mb-8">
                        <button type="button" @click="tab='investor'" :class="tabClass('investor')" class="flex-1 px-4 py-2.5 sm:py-3 rounded-lg border-2 font-medium transition-all duration-200 text-sm sm:text-base">Investor</button>
                        <button type="button" @click="tab='reseller'" :class="tabClass('reseller')" class="flex-1 px-4 py-2.5 sm:py-3 rounded-lg border-2 font-medium transition-all duration-200 text-sm sm:text-base">Reseller</button>
                    </div>

                    <!-- Investor Form -->
                    <form method="POST" action="{{ route('register.post') }}" class="space-y-4 sm:space-y-6" novalidate x-show="tab==='investor'" x-cloak>
                        @csrf
                        <input type="hidden" name="role" value="investor">
                        <div class="grid md:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                    Name
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('name', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'name'" x-cloak x-transition @click.away="tooltip = null" class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Full Name Format:</strong> Enter your complete name including first name and last name. Example: John Doe or Muhammad Ali</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input type="text" name="name" id="investor-name" value="{{ old('name') }}" class="form-input text-sm sm:text-base" required placeholder="Enter your full name" x-on:input="validateName($event.target.value, 'investor')" x-on:blur="validateName($event.target.value, 'investor')">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="nameValidated.investor && nameStatus.investor === 'valid'" class="text-green-500">✓</span>
                                <span x-show="nameValidated.investor && nameStatus.investor === 'invalid'" class="text-red-500">✗</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: First Name Last Name (e.g., John Doe)</p>
                        <p x-show="nameValidated.investor && nameStatus.investor === 'invalid'" x-cloak class="text-xs text-red-500 mt-1" x-text="nameMessage.investor"></p>
                        <p x-show="nameValidated.investor && nameStatus.investor === 'valid'" x-cloak class="text-xs text-green-500 mt-1" x-text="nameMessage.investor"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Email
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('email', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'email'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Email Requirements:</strong> Enter a valid email address. The system will verify that the email exists and is available. Format: user@example.com</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input type="email" name="email" id="investor-email" value="{{ old('email') }}" class="form-input text-sm sm:text-base" required placeholder="example@email.com" x-on:input.debounce.500ms="validateEmail($event.target.value, 'investor')" x-on:blur="validateEmail($event.target.value, 'investor')">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="emailValidated.investor && emailStatus.investor === 'valid'" class="text-green-500">✓</span>
                                <span x-show="emailValidated.investor && emailStatus.investor === 'invalid'" class="text-red-500">✗</span>
                                <span x-show="emailStatus.investor === 'checking'" class="animate-spin text-gray-400">⟳</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: valid email address (e.g., user@example.com)</p>
                        <p x-show="emailValidated.investor && emailStatus.investor === 'invalid'" x-cloak class="text-xs text-red-500 mt-1" x-text="emailMessage.investor"></p>
                        <p x-show="emailValidated.investor && emailStatus.investor === 'valid'" x-cloak class="text-xs text-green-500 mt-1" x-text="emailMessage.investor"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Phone
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('phone', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'phone'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Phone Format:</strong> Include country code with + sign, followed by space and number. Examples: +92 370 1346038, +1 555 1234567</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input
                                type="tel"
                                name="phone"
                                id="investor-phone"
                                value="{{ old('phone') }}"
                                class="form-input text-sm sm:text-base"
                                required
                                placeholder="+92 370 1346038"
                                data-intl-tel-input
                                x-on:input.debounce.500ms="validatePhone($event.target.value, 'investor')"
                                x-on:blur="validatePhone($event.target.value, 'investor')"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="phoneValidated.investor && phoneStatus.investor === 'valid'" class="text-green-500">✓</span>
                                <span x-show="phoneValidated.investor && phoneStatus.investor === 'invalid'" class="text-red-500">✗</span>
                                <span x-show="phoneStatus.investor === 'checking'" class="animate-spin text-gray-400">⟳</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: Country Code + Space + Number (e.g., +92 370 1346038)</p>
                        <p x-show="phoneValidated.investor && phoneStatus.investor === 'invalid'" x-cloak class="text-xs text-red-500 mt-1" x-text="phoneMessage.investor"></p>
                        <p x-show="phoneValidated.investor && phoneStatus.investor === 'valid'" x-cloak class="text-xs text-green-500 mt-1" x-text="phoneMessage.investor"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Password
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('password', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </label>
                        <div class="relative">
                            <input 
                                :type="showPassword.investor ? 'text' : 'password'"
                                name="password" 
                                id="investor-password" 
                                class="form-input text-sm sm:text-base pr-20" 
                                required 
                                placeholder="Minimum 8 characters" 
                                autocomplete="new-password"
                                x-on:input="validatePassword($event.target.value, 'investor')"
                                x-on:focus="showPasswordTooltip.investor = true"
                                x-on:blur="showPasswordTooltip.investor = false"
                                x-on:keydown="checkCapsLock($event, 'investor')"
                                x-on:keyup="checkCapsLock($event, 'investor')"
                                x-ref="investorPassword"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                                <!-- Caps Lock Indicator -->
                                <span x-show="capsLockActive.investor" x-cloak class="text-xs text-amber-600 font-semibold" title="Caps Lock is ON">⇪</span>
                                <!-- Num Lock Indicator -->
                                <span x-show="numLockActive.investor" x-cloak class="text-xs text-blue-600 font-semibold" title="Num Lock is ON">🔢</span>
                                <!-- Password Valid Indicator -->
                                <span x-show="isPasswordValid('investor')" class="text-green-500">✓</span>
                                <!-- Password Visibility Toggle (Always visible) -->
                                <button 
                                    type="button"
                                    @click="showPassword.investor = !showPassword.investor"
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                    :title="showPassword.investor ? 'Hide password' : 'Show password'"
                                    tabindex="-1"
                                >
                                    <!-- Eye Icon (Visible) -->
                                    <svg x-show="showPassword.investor" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye Slash Icon (Hidden) -->
                                    <svg x-show="!showPassword.investor" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m0 0L3 3m13.561 13.561A10.05 10.05 0 0121 12c0-4.478-2.943-8.268-7-9.543a9.97 9.97 0 00-3.029 1.563m13.561 13.561L21 21M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Password Requirements Tooltip (shown on focus) -->
                        <div 
                            x-show="showPasswordTooltip.investor" 
                            x-cloak 
                            x-transition
                            class="mt-2 p-3 bg-white rounded-lg border border-gray-200 shadow-lg"
                        >
                            <p class="text-xs font-semibold text-gray-700 mb-2">Password Requirements:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.investor.hasUpperCase ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.investor.hasUpperCase ? 'text-green-600 font-medium' : 'text-gray-600'">Uppercase (A-Z)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.investor.hasLowerCase ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.investor.hasLowerCase ? 'text-green-600 font-medium' : 'text-gray-600'">Lowercase (a-z)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.investor.hasNumber ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.investor.hasNumber ? 'text-green-600 font-medium' : 'text-gray-600'">Number (0-9)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.investor.hasSpecialChar ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.investor.hasSpecialChar ? 'text-green-600 font-medium' : 'text-gray-600'">Special (!@#...)</span>
                                </div>
                                <div class="flex items-center gap-1.5 sm:col-span-2">
                                    <span :class="passwordCriteria.investor.minLength ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.investor.minLength ? 'text-green-600 font-medium' : 'text-gray-600'">Minimum 8 characters</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Confirm Password
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                :type="showPassword.confirmInvestor ? 'text' : 'password'"
                                name="password_confirmation" 
                                id="investor-password-confirmation"
                                class="form-input text-sm sm:text-base pr-20" 
                                required 
                                placeholder="Re-enter your password"
                                autocomplete="new-password"
                                x-on:keydown="checkCapsLock($event, 'confirmInvestor')"
                                x-on:keyup="checkCapsLock($event, 'confirmInvestor')"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                                <!-- Caps Lock Indicator -->
                                <span x-show="capsLockActive.confirmInvestor" x-cloak class="text-xs text-amber-600 font-semibold" title="Caps Lock is ON">⇪</span>
                                <!-- Num Lock Indicator -->
                                <span x-show="numLockActive.confirmInvestor" x-cloak class="text-xs text-blue-600 font-semibold" title="Num Lock is ON">🔢</span>
                                <!-- Password Visibility Toggle (Always visible) -->
                                <button 
                                    type="button"
                                    @click="showPassword.confirmInvestor = !showPassword.confirmInvestor"
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                    :title="showPassword.confirmInvestor ? 'Hide password' : 'Show password'"
                                    tabindex="-1"
                                >
                                    <!-- Eye Icon (Visible) -->
                                    <svg x-show="showPassword.confirmInvestor" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye Slash Icon (Hidden) -->
                                    <svg x-show="!showPassword.confirmInvestor" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m0 0L3 3m13.561 13.561A10.05 10.05 0 0121 12c0-4.478-2.943-8.268-7-9.543a9.97 9.97 0 00-3.029 1.563m13.561 13.561L21 21M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Must match the password above</p>
                    </div>
                    
                    <!-- Referral Code Field (for Investors) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Referral Code
                            <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('referral', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'referral'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Referral Code:</strong> If you have a referral code from a reseller, enter it here. Format: RSL followed by numbers (e.g., RSL1001). This links your account to the reseller.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <input 
                            type="text" 
                            name="referral_code" 
                            id="referralCode"
                            value="{{ old('referral_code', request('ref')) }}" 
                            class="form-input text-sm sm:text-base {{ $errors->has('referral_code') ? 'border-red-500' : '' }}"
                            placeholder="Enter reseller referral code (e.g., RSL1001)"
                            x-on:input="validateReferralCode($event.target.value)"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Format: RSL followed by numbers (e.g., RSL1001). Have a referral code from a reseller? Enter it here to link your account.
                        </p>
                        @error('referral_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div id="referralCodeStatus" class="mt-2 hidden">
                            <p id="referralCodeMessage" class="text-sm"></p>
                        </div>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 text-xs sm:text-sm text-gray-700">
                        <input type="checkbox" name="terms" class="mt-1 rounded border-gray-300 w-4 h-4" required>
                        <span>
                            I have read and agree to RWAMP's
                            <a href="{{ route('terms.of.service') }}" class="text-primary underline">Terms of Service</a>,
                            <a href="{{ route('privacy.policy') }}" class="text-primary underline">Privacy Policy</a>, and
                            <a href="{{ route('disclaimer') }}" class="text-primary underline">Disclaimer</a>.
                        </span>
                    </label>

                    @php
                        $showRecaptcha = config('services.recaptcha.site_key') && 
                                        !in_array(request()->getHost(), ['localhost', '127.0.0.1']) &&
                                        !str_contains(config('app.url', ''), 'localhost') &&
                                        !str_contains(config('app.url', ''), '127.0.0.1') &&
                                        config('app.env') !== 'local';
                    @endphp
                    @if($showRecaptcha)
                        <div class="mt-4">
                            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-theme="light"></div>
                            @error('g-recaptcha-response')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <button type="submit" class="w-full btn-primary text-sm sm:text-base py-3 sm:py-4">Sign Up</button>
                    </form>

                    <!-- Reseller Application Form -->
                    <form method="POST" action="{{ route('register.post') }}" class="space-y-4 sm:space-y-6" novalidate x-show="tab==='reseller'" x-cloak>
                        @csrf
                        <input type="hidden" name="role" value="reseller">
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 sm:mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Application Process:</strong> Your reseller application will be reviewed by our admin team. You will receive an email notification once your application is approved or rejected.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                    Full Name
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('reseller-name', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'reseller-name'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Full Name Format:</strong> Enter your complete name including first name and last name. Example: John Doe or Muhammad Ali</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input type="text" name="name" id="reseller-name" value="{{ old('name') }}" class="form-input text-sm sm:text-base" required placeholder="Enter your full name" x-on:input="validateName($event.target.value, 'reseller')" x-on:blur="validateName($event.target.value, 'reseller')">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="nameValidated.reseller && nameStatus.reseller === 'valid'" class="text-green-500">✓</span>
                                <span x-show="nameValidated.reseller && nameStatus.reseller === 'invalid'" class="text-red-500">✗</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: First Name Last Name (e.g., John Doe)</p>
                        <p x-show="nameValidated.reseller && nameStatus.reseller === 'invalid'" x-cloak class="text-xs text-red-500 mt-1" x-text="nameMessage.reseller"></p>
                        <p x-show="nameValidated.reseller && nameStatus.reseller === 'valid'" x-cloak class="text-xs text-green-500 mt-1" x-text="nameMessage.reseller"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Email Address
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('reseller-email', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'reseller-email'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Email Requirements:</strong> Enter a valid email address. The system will verify that the email exists and is available. You'll receive application notifications at this email.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input type="email" name="email" id="reseller-email" value="{{ old('email') }}" class="form-input text-sm sm:text-base" required placeholder="example@email.com" x-on:input.debounce.500ms="validateEmail($event.target.value, 'reseller')" x-on:blur="validateEmail($event.target.value, 'reseller')">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="emailValidated.reseller && emailStatus.reseller === 'valid'" class="text-green-500">✓</span>
                                <span x-show="emailValidated.reseller && emailStatus.reseller === 'invalid'" class="text-red-500">✗</span>
                                <span x-show="emailStatus.reseller === 'checking'" class="animate-spin text-gray-400">⟳</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: valid email address (e.g., user@example.com). You'll receive notifications at this email.</p>
                        <p x-show="emailValidated.reseller && emailStatus.reseller === 'invalid'" x-cloak class="text-xs text-red-500 mt-1" x-text="emailMessage.reseller"></p>
                        <p x-show="emailValidated.reseller && emailStatus.reseller === 'valid'" x-cloak class="text-xs text-green-500 mt-1" x-text="emailMessage.reseller"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Phone Number
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('reseller-phone', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'reseller-phone'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Phone Format:</strong> Include country code with + sign, followed by space and number. Examples: +92 370 1346038, +1 555 1234567</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <div class="relative">
                            <input
                                type="tel"
                                name="phone"
                                id="reseller-phone"
                                value="{{ old('phone') }}"
                                class="form-input text-sm sm:text-base"
                                required
                                placeholder="+92 370 1346038"
                                data-intl-tel-input
                                x-on:input.debounce.500ms="validatePhone($event.target.value, 'reseller')"
                                x-on:blur="validatePhone($event.target.value, 'reseller')"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span x-show="phoneValidated.reseller && phoneStatus.reseller === 'valid'" class="text-green-500">✓</span>
                                <span x-show="phoneValidated.reseller && phoneStatus.reseller === 'invalid'" class="text-red-500">✗</span>
                                <span x-show="phoneStatus.reseller === 'checking'" class="animate-spin text-gray-400">⟳</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Format: Country Code + Space + Number (e.g., +92 370 1346038 or +1 555 1234567)</p>
                        <p x-show="phoneValidated.reseller && phoneStatus.reseller === 'invalid'" x-cloak class="text-xs text-red-500 mt-1" x-text="phoneMessage.reseller"></p>
                        <p x-show="phoneValidated.reseller && phoneStatus.reseller === 'valid'" x-cloak class="text-xs text-green-500 mt-1" x-text="phoneMessage.reseller"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Password
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('reseller-password', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </label>
                        <div class="relative">
                            <input 
                                :type="showPassword.reseller ? 'text' : 'password'"
                                name="password" 
                                id="reseller-password" 
                                class="form-input text-sm sm:text-base pr-20" 
                                required 
                                placeholder="Minimum 8 characters" 
                                autocomplete="new-password"
                                x-on:input="validatePassword($event.target.value, 'reseller')"
                                x-on:focus="showPasswordTooltip.reseller = true"
                                x-on:blur="showPasswordTooltip.reseller = false"
                                x-on:keydown="checkCapsLock($event, 'reseller')"
                                x-on:keyup="checkCapsLock($event, 'reseller')"
                                x-ref="resellerPassword"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                                <!-- Caps Lock Indicator -->
                                <span x-show="capsLockActive.reseller" x-cloak class="text-xs text-amber-600 font-semibold" title="Caps Lock is ON">⇪</span>
                                <!-- Num Lock Indicator -->
                                <span x-show="numLockActive.reseller" x-cloak class="text-xs text-blue-600 font-semibold" title="Num Lock is ON">🔢</span>
                                <!-- Password Valid Indicator -->
                                <span x-show="isPasswordValid('reseller')" class="text-green-500">✓</span>
                                <!-- Password Visibility Toggle (Always visible) -->
                                <button 
                                    type="button"
                                    @click="showPassword.reseller = !showPassword.reseller"
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                    :title="showPassword.reseller ? 'Hide password' : 'Show password'"
                                    tabindex="-1"
                                >
                                    <!-- Eye Icon (Visible) -->
                                    <svg x-show="showPassword.reseller" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye Slash Icon (Hidden) -->
                                    <svg x-show="!showPassword.reseller" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m0 0L3 3m13.561 13.561A10.05 10.05 0 0121 12c0-4.478-2.943-8.268-7-9.543a9.97 9.97 0 00-3.029 1.563m13.561 13.561L21 21M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Password Requirements Tooltip (shown on focus) -->
                        <div 
                            x-show="showPasswordTooltip.reseller" 
                            x-cloak 
                            x-transition
                            class="mt-2 p-3 bg-white rounded-lg border border-gray-200 shadow-lg"
                        >
                            <p class="text-xs font-semibold text-gray-700 mb-2">Password Requirements:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.reseller.hasUpperCase ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.reseller.hasUpperCase ? 'text-green-600 font-medium' : 'text-gray-600'">Uppercase (A-Z)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.reseller.hasLowerCase ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.reseller.hasLowerCase ? 'text-green-600 font-medium' : 'text-gray-600'">Lowercase (a-z)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.reseller.hasNumber ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.reseller.hasNumber ? 'text-green-600 font-medium' : 'text-gray-600'">Number (0-9)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span :class="passwordCriteria.reseller.hasSpecialChar ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.reseller.hasSpecialChar ? 'text-green-600 font-medium' : 'text-gray-600'">Special (!@#...)</span>
                                </div>
                                <div class="flex items-center gap-1.5 sm:col-span-2">
                                    <span :class="passwordCriteria.reseller.minLength ? 'text-green-500' : 'text-gray-400'" class="font-bold">✓</span>
                                    <span :class="passwordCriteria.reseller.minLength ? 'text-green-600 font-medium' : 'text-gray-600'">Minimum 8 characters</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Confirm Password
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                :type="showPassword.confirmReseller ? 'text' : 'password'"
                                name="password_confirmation" 
                                id="reseller-password-confirmation"
                                class="form-input text-sm sm:text-base pr-20" 
                                required 
                                placeholder="Re-enter your password"
                                autocomplete="new-password"
                                x-on:keydown="checkCapsLock($event, 'confirmReseller')"
                                x-on:keyup="checkCapsLock($event, 'confirmReseller')"
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                                <!-- Caps Lock Indicator -->
                                <span x-show="capsLockActive.confirmReseller" x-cloak class="text-xs text-amber-600 font-semibold" title="Caps Lock is ON">⇪</span>
                                <!-- Num Lock Indicator -->
                                <span x-show="numLockActive.confirmReseller" x-cloak class="text-xs text-blue-600 font-semibold" title="Num Lock is ON">🔢</span>
                                <!-- Password Visibility Toggle (Always visible) -->
                                <button 
                                    type="button"
                                    @click="showPassword.confirmReseller = !showPassword.confirmReseller"
                                    class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                    :title="showPassword.confirmReseller ? 'Hide password' : 'Show password'"
                                    tabindex="-1"
                                >
                                    <!-- Eye Icon (Visible) -->
                                    <svg x-show="showPassword.confirmReseller" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye Slash Icon (Hidden) -->
                                    <svg x-show="!showPassword.confirmReseller" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m0 0L3 3m13.561 13.561A10.05 10.05 0 0121 12c0-4.478-2.943-8.268-7-9.543a9.97 9.97 0 00-3.029 1.563m13.561 13.561L21 21M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Must match the password above exactly</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Company Name
                            <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('company', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'company'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Company Name:</strong> Enter your business or company name if you're applying as a business entity. This field is optional.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-input text-sm sm:text-base" placeholder="Your company name">
                        <p class="text-xs text-gray-500 mt-1">Enter your business or company name if applicable</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Investment Capacity (PKR)
                            <span class="text-red-500">*</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('investment', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'investment'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Investment Capacity:</strong> Enter your estimated investment capacity in Pakistani Rupees (PKR). Examples: 50000, 100000, 500000. This helps us understand your business scale.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <input type="text" name="investment_capacity" value="{{ old('investment_capacity') }}" class="form-input text-sm sm:text-base" required placeholder="e.g., 50000 or 100000">
                        <p class="text-xs text-gray-500 mt-1">Format: Enter amount in PKR (e.g., 50000, 100000, 500000). This is your estimated investment capacity.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                            Experience
                            <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                            <div class="relative">
                                <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('experience', $event)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div x-show="tooltip === 'experience'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                    <p><strong>Experience:</strong> Describe your relevant experience in cryptocurrency trading, sales, marketing, or business development. This field is optional but helps with your application review.</p>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                        <div class="border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <textarea name="experience" rows="4" class="form-input text-sm sm:text-base" placeholder="Describe your experience in cryptocurrency, sales, or related fields...">{{ old('experience') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Describe your relevant experience in cryptocurrency trading, sales, marketing, or business development</p>
                            </div>
                        </div>

                        <label class="flex items-start gap-3 text-xs sm:text-sm text-gray-700">
                            <input type="checkbox" name="terms" class="mt-1 rounded border-gray-300 w-4 h-4" required>
                            <span>
                                I have read and agree to RWAMP's
                                <a href="{{ route('terms.of.service') }}" class="text-primary underline">Terms of Service</a>,
                                <a href="{{ route('privacy.policy') }}" class="text-primary underline">Privacy Policy</a>, and
                                <a href="{{ route('disclaimer') }}" class="text-primary underline">Disclaimer</a>.
                            </span>
                        </label>

                        @php
                            $showRecaptcha = config('services.recaptcha.site_key') && 
                                            !in_array(request()->getHost(), ['localhost', '127.0.0.1']) &&
                                            !str_contains(config('app.url', ''), 'localhost') &&
                                            !str_contains(config('app.url', ''), '127.0.0.1') &&
                                            config('app.env') !== 'local';
                        @endphp
                        @if($showRecaptcha)
                            <div class="mt-4">
                                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}" data-theme="light"></div>
                                @error('g-recaptcha-response')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <button type="submit" class="w-full btn-primary text-sm sm:text-base py-3 sm:py-4">Submit Application</button>
                    </form>

                    <p class="text-center text-xs sm:text-sm text-gray-700 mt-6">Already have an account?
                        <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Login</a>
                    </p>
                </div>
            </div>
            
            <!-- Mobile Promotional Section -->
            <div class="lg:hidden space-y-4 text-white mt-6">
                <div class="bg-gradient-to-br from-primary/20 to-secondary/20 backdrop-blur-lg rounded-2xl p-6 border border-primary/30">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="coin-ring">
                            <img src="{{ asset('images/logo.png') }}" alt="RWAMP" class="w-12 h-12 rounded-full rwamp-coin-logo">
                        </div>
                        <div>
                            <h2 class="text-xl font-montserrat font-bold">RWAMP Coin</h2>
                            <p class="text-white/80 text-xs">Start Your Investment Journey</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-start gap-2">
                            <span class="text-xl">💎</span>
                            <div>
                                <h3 class="font-semibold text-sm">Premium Platform</h3>
                                <p class="text-white/70 text-xs">Join thousands of investors</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-xl">📊</span>
                            <div>
                                <h3 class="font-semibold text-sm">Expert Analysis</h3>
                                <p class="text-white/70 text-xs">Professional market insights</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-xl">🔒</span>
                            <div>
                                <h3 class="font-semibold text-sm">Bank-Level Security</h3>
                                <p class="text-white/70 text-xs">Advanced encryption</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/10 rounded-lg p-3 border border-white/20">
                        <h4 class="font-semibold text-sm mb-2 flex items-center gap-1">
                            <span>🎯</span> Sign Up Benefits
                        </h4>
                        <ul class="space-y-1 text-xs text-white/80">
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Start investing easily
                            </li>
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Exclusive packages
                            </li>
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Earn passive income
                            </li>
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Referral rewards
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gradient-to-r from-primary/30 to-secondary/30 rounded-lg p-3 border border-primary/40 mt-4">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">🚀</span>
                            <div>
                                <h4 class="font-bold text-sm">Limited Offer</h4>
                                <p class="text-white/80 text-xs">Bonus coins on first investment!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $showRecaptcha = config('services.recaptcha.site_key') && 
                    !in_array(request()->getHost(), ['localhost', '127.0.0.1']) &&
                    !str_contains(config('app.url', ''), 'localhost') &&
                    !str_contains(config('app.url', ''), '127.0.0.1') &&
                    config('app.env') !== 'local';
@endphp
@if($showRecaptcha)
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
<!-- signupTabs is registered in resources/js/app.js via Alpine -->
@endpush
