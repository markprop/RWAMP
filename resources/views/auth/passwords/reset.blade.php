@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <h1 class="text-2xl font-montserrat font-bold mb-4 text-center">Reset Password</h1>
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4" novalidate x-data="resetPasswordForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <div class="relative">
                        <input 
                            name="password" 
                            :type="showPassword.new ? 'text' : 'password'" 
                            class="form-input pr-20" 
                            required 
                            autocomplete="new-password"
                            x-on:keydown="checkCapsLock($event, 'new')"
                            x-on:keyup="checkCapsLock($event, 'new')"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                            <!-- Caps Lock Indicator -->
                            <span x-show="capsLockActive.new" x-cloak class="text-xs text-amber-600 font-semibold" title="Caps Lock is ON">⇪</span>
                            <!-- Num Lock Indicator -->
                            <span x-show="numLockActive.new" x-cloak class="text-xs text-blue-600 font-semibold" title="Num Lock is ON">🔢</span>
                            <!-- Password Visibility Toggle (Always visible) -->
                            <button 
                                type="button"
                                @click="showPassword.new = !showPassword.new"
                                class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                :title="showPassword.new ? 'Hide password' : 'Show password'"
                                tabindex="-1"
                            >
                                <!-- Eye Icon (Visible) -->
                                <svg x-show="showPassword.new" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <!-- Eye Slash Icon (Hidden) -->
                                <svg x-show="!showPassword.new" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m0 0L3 3m13.561 13.561A10.05 10.05 0 0121 12c0-4.478-2.943-8.268-7-9.543a9.97 9.97 0 00-3.029 1.563m13.561 13.561L21 21M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                        <input 
                            name="password_confirmation" 
                            :type="showPassword.confirm ? 'text' : 'password'" 
                            class="form-input pr-20" 
                            required 
                            autocomplete="new-password"
                            x-on:keydown="checkCapsLock($event, 'confirm')"
                            x-on:keyup="checkCapsLock($event, 'confirm')"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-2">
                            <!-- Caps Lock Indicator -->
                            <span x-show="capsLockActive.confirm" x-cloak class="text-xs text-amber-600 font-semibold" title="Caps Lock is ON">⇪</span>
                            <!-- Num Lock Indicator -->
                            <span x-show="numLockActive.confirm" x-cloak class="text-xs text-blue-600 font-semibold" title="Num Lock is ON">🔢</span>
                            <!-- Password Visibility Toggle (Always visible) -->
                            <button 
                                type="button"
                                @click="showPassword.confirm = !showPassword.confirm"
                                class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors"
                                :title="showPassword.confirm ? 'Hide password' : 'Show password'"
                                tabindex="-1"
                            >
                                <!-- Eye Icon (Visible) -->
                                <svg x-show="showPassword.confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <!-- Eye Slash Icon (Hidden) -->
                                <svg x-show="!showPassword.confirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m0 0L3 3m3.29 3.29L3 3m3.29 3.29l3.29 3.29m0 0L3 3m13.561 13.561A10.05 10.05 0 0121 12c0-4.478-2.943-8.268-7-9.543a9.97 9.97 0 00-3.029 1.563m13.561 13.561L21 21M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="w-full btn-primary">Update Password</button>
            </form>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('resetPasswordForm', function() {
        return {
            showPassword: {
                new: false,
                confirm: false
            },
        capsLockActive: {
            new: false,
            confirm: false
        },
        numLockActive: {
            new: false,
            confirm: false
        },
            checkCapsLock: function(event, field) {
                // Check Caps Lock
                if (event.getModifierState && typeof event.getModifierState === 'function') {
                    this.capsLockActive[field] = event.getModifierState('CapsLock');
                } else {
                    // Fallback for older browsers
                    const key = event.key || event.keyCode;
                    const shift = event.shiftKey;
                    if (key && key.length === 1) {
                        if (key >= 'A' && key <= 'Z' && !shift) {
                            this.capsLockActive[field] = true;
                        } else if (key >= 'a' && key <= 'z' && shift) {
                            this.capsLockActive[field] = true;
                        } else {
                            this.capsLockActive[field] = false;
                        }
                    }
                }
                
                // Check Num Lock (approximate detection)
                const key = event.key || event.keyCode;
                if (key && (key >= '0' && key <= '9' || (event.keyCode >= 96 && event.keyCode <= 105))) {
                    this.numLockActive[field] = true;
                }
            }
        };
    });
});
</script>
@endpush

