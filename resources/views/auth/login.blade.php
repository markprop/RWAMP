@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <!-- Promotional Section -->
            <div class="hidden lg:block space-y-6 text-white">
                <div class="bg-gradient-to-br from-primary/20 to-secondary/20 backdrop-blur-lg rounded-2xl p-8 border border-primary/30 shadow-2xl">
                    <div class="flex items-center gap-4 mb-6">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP" class="w-16 h-16 rounded-full border-2 border-primary">
                        <div>
                            <h2 class="text-3xl font-montserrat font-bold text-white">RWAMP Coin</h2>
                            <p class="text-white/80 text-sm">Your Gateway to Profitable Investments</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">💰</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">High Profit Potential</h3>
                                <p class="text-white/70 text-sm">Invest in RWAMP coins and unlock exceptional returns with our proven investment strategies.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">🚀</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">Secure & Transparent</h3>
                                <p class="text-white/70 text-sm">Blockchain-powered security ensures your investments are safe and transactions are transparent.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                                <span class="text-2xl">📈</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-1">Real-Time Growth</h3>
                                <p class="text-white/70 text-sm">Track your portfolio performance in real-time and make informed investment decisions.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/10 rounded-lg p-4 border border-white/20">
                        <h4 class="font-semibold mb-2 flex items-center gap-2">
                            <span>✨</span> Why Login?
                        </h4>
                        <ul class="space-y-2 text-sm text-white/80">
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Access your personalized investment dashboard
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Manage your portfolio and track profits
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Get exclusive investment opportunities
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Receive real-time market updates
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-primary">✓</span>
                                Join our community of successful investors
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center border border-white/20">
                        <div class="text-3xl mb-2">🎯</div>
                        <div class="text-2xl font-bold text-primary">100%</div>
                        <div class="text-xs text-white/70">Secure</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center border border-white/20">
                        <div class="text-3xl mb-2">⭐</div>
                        <div class="text-2xl font-bold text-primary">24/7</div>
                        <div class="text-xs text-white/70">Support</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur rounded-lg p-4 text-center border border-white/20">
                        <div class="text-3xl mb-2">💎</div>
                        <div class="text-2xl font-bold text-primary">1000+</div>
                        <div class="text-xs text-white/70">Investors</div>
                    </div>
                </div>
            </div>
            
            <!-- Login Form Section -->
            <div class="w-full max-w-md mx-auto lg:mx-0">
                <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-6 sm:p-8 card-hover animate-fadeInUp">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP" class="w-14 h-14 sm:w-16 sm:h-16 mx-auto rounded-full mb-3">
                <h1 class="text-xl sm:text-2xl font-montserrat font-bold">Welcome back</h1>
                <p class="text-sm sm:text-base text-gray-600">Login to continue</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4" novalidate x-data="loginForm">
                @csrf
                <div class="flex gap-2 justify-center">
                    <button type="button" onclick="document.getElementById('login-role').value='investor'; this.classList.add('border-primary','text-primary','bg-primary/5'); document.getElementById('role-reseller').classList.remove('border-primary','text-primary','bg-primary/5'); document.getElementById('role-reseller').classList.add('border-gray-200','text-gray-700');" id="role-investor" class="flex-1 px-4 py-2.5 rounded-lg border-2 border-primary text-primary bg-primary/5 font-medium transition-all duration-200 text-sm sm:text-base">Investor</button>
                    <button type="button" onclick="document.getElementById('login-role').value='reseller'; this.classList.add('border-primary','text-primary','bg-primary/5'); document.getElementById('role-investor').classList.remove('border-primary','text-primary','bg-primary/5'); document.getElementById('role-investor').classList.add('border-gray-200','text-gray-700');" id="role-reseller" class="flex-1 px-4 py-2.5 rounded-lg border-2 border-gray-200 text-gray-700 font-medium transition-all duration-200 text-sm sm:text-base">Reseller</button>
                </div>
                <input type="hidden" name="role" id="login-role" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                        Email
                        <span class="text-red-500">*</span>
                        <div class="relative group">
                            <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('email', $event)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div x-show="tooltip === 'email'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                <p><strong>Email:</strong> Enter the email address you used to register your account. Make sure to use the correct email format (e.g., user@example.com)</p>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                    <div class="border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <input name="email" type="email" value="{{ old('email') }}" class="form-input text-sm sm:text-base" required autocomplete="email" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                        Password
                        <span class="text-red-500">*</span>
                        <div class="relative group">
                            <svg class="h-4 w-4 text-gray-400 cursor-pointer hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" @click.stop.prevent="showTooltip('password', $event)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div x-show="tooltip === 'password'" @click.away="tooltip = null" x-cloak x-transition class="absolute z-50 w-64 p-3 text-xs text-white bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 transform -translate-x-1/2 mb-2" style="display: none;">
                                <p><strong>Password:</strong> Enter the password associated with your account. If you've forgotten your password, click "Forgot Password?" below to reset it.</p>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                    <div class="border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                    <input name="password" type="password" class="form-input text-sm sm:text-base" required autocomplete="current-password" />
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-0">
                    <label class="inline-flex items-center gap-2 text-xs sm:text-sm text-gray-700">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 w-4 h-4" /> Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs sm:text-sm text-primary hover:underline">Forgot Password?</a>
                </div>

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

                <button type="submit" class="w-full btn-primary text-sm sm:text-base py-3 sm:py-4">Login</button>
            </form>

            <p class="text-center text-xs sm:text-sm text-gray-700 mt-6">Don't have an account?
                <a href="{{ route('register') }}" class="text-primary hover:underline font-medium">Sign up</a>
            </p>
        </div>
            </div>
            
            <!-- Mobile Promotional Section -->
            <div class="lg:hidden space-y-4 text-white mt-6">
                <div class="bg-gradient-to-br from-primary/20 to-secondary/20 backdrop-blur-lg rounded-2xl p-6 border border-primary/30">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP" class="w-12 h-12 rounded-full border-2 border-primary">
                        <div>
                            <h2 class="text-xl font-montserrat font-bold">RWAMP Coin</h2>
                            <p class="text-white/80 text-xs">Your Gateway to Profitable Investments</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="flex items-start gap-2">
                            <span class="text-xl">💰</span>
                            <div>
                                <h3 class="font-semibold text-sm">High Profit Potential</h3>
                                <p class="text-white/70 text-xs">Exceptional returns with proven strategies</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="text-xl">🚀</span>
                            <div>
                                <h3 class="font-semibold text-sm">Secure & Transparent</h3>
                                <p class="text-white/70 text-xs">Blockchain-powered security</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/10 rounded-lg p-3 border border-white/20">
                        <h4 class="font-semibold text-sm mb-2 flex items-center gap-1">
                            <span>✨</span> Login Benefits
                        </h4>
                        <ul class="space-y-1 text-xs text-white/80">
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Access dashboard
                            </li>
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Track profits
                            </li>
                            <li class="flex items-center gap-1">
                                <span class="text-primary">✓</span> Exclusive opportunities
                            </li>
                        </ul>
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
@endpush


