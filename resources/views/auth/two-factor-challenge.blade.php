@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <h1 class="text-2xl font-montserrat font-bold mb-4 text-center">Two-Factor Authentication</h1>
            <p class="text-gray-600 text-sm mb-6 text-center">Enter the 6‑digit verification code from your authenticator app.</p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ url('/two-factor-challenge') }}" class="space-y-4" novalidate>
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Code</label>
                    <input name="code" type="text" inputmode="numeric" autocomplete="one-time-code" class="form-input" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Recovery Code</label>
                    <input name="recovery_code" type="text" class="form-input" />
                    <p class="text-xs text-gray-500 mt-1">Use a recovery code if you can’t access your authenticator app.</p>
                </div>

                <button type="submit" class="w-full btn-primary">Verify</button>
            </form>
        </div>
    </div>
</div>
@endsection

