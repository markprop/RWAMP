@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Create your account</h1>
            <p class="text-white/80 mt-2">Join as an Investor or a Reseller</p>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="signupTabs">
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        @if(request('ref'))
            <div class="mb-6 rounded-lg border border-blue-300 bg-blue-50 text-blue-800 px-4 py-3">
                <p><strong>Referral Link Detected!</strong> The referral code from the URL has been pre-filled. You can modify it if needed.</p>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 animate-fadeInUp">
            <div class="flex gap-2 mb-8">
                <button type="button" @click="tab='investor'" :class="tabClass('investor')">Investor</button>
                <button type="button" @click="tab='reseller'" :class="tabClass('reseller')">Reseller</button>
            </div>

            <form method="POST" action="{{ route('register.post') }}" class="space-y-6" novalidate>
                @csrf
                <input type="hidden" name="role" :value="tab">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input" required>
                    </div>
                    
                    <!-- Referral Code Field (for Investors) -->
                    <div class="md:col-span-2" x-show="tab==='investor'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Referral Code <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                        </label>
                        <input 
                            type="text" 
                            name="referral_code" 
                            id="referralCode"
                            value="{{ old('referral_code', request('ref')) }}" 
                            class="form-input {{ $errors->has('referral_code') ? 'border-red-500' : '' }}"
                            placeholder="Enter reseller referral code (e.g., RSL1001)"
                            x-on:input="validateReferralCode($event.target.value)"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Have a referral code from a reseller? Enter it here to link your account. You can also use a referral link like: <code class="text-xs bg-gray-100 px-1 rounded">/register?ref=RSL1001</code>
                        </p>
                        @error('referral_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div id="referralCodeStatus" class="mt-2 hidden">
                            <p id="referralCodeMessage" class="text-sm"></p>
                        </div>
                    </div>

                    <!-- Reseller-only fields -->
                    <div x-show="tab==='reseller'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-input">
                    </div>
                    <div x-show="tab==='reseller'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Investment Capacity (USD)</label>
                        <input type="number" name="investment_capacity" value="{{ old('investment_capacity') }}" class="form-input" min="0">
                    </div>
                    <div class="md:col-span-2" x-show="tab==='reseller'" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Experience</label>
                        <textarea name="experience" rows="3" class="form-input">{{ old('experience') }}</textarea>
                    </div>
                </div>

                <label class="flex items-start gap-3 text-sm text-gray-700">
                    <input type="checkbox" name="terms" class="mt-1 rounded border-gray-300" required>
                    <span>
                        I have read and agree to RWAMP’s
                        <a href="{{ route('terms.of.service') }}" class="text-primary underline">Terms of Service</a>,
                        <a href="{{ route('privacy.policy') }}" class="text-primary underline">Privacy Policy</a>, and
                        <a href="{{ route('disclaimer') }}" class="text-primary underline">Disclaimer</a>.
                    </span>
                </label>

                <button type="submit" class="w-full btn-primary">Sign Up</button>
            </form>

            <p class="text-center text-sm text-gray-700 mt-6">Already have an account?
                <a href="{{ route('login') }}" class="text-primary hover:underline">Login</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- signupTabs is registered in resources/js/app.js via Alpine -->
@endpush

