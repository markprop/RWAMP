@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <h1 class="text-2xl font-montserrat font-bold mb-4 text-center">Forgot Password</h1>
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate>
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        name="email" 
                        type="email" 
                        value="{{ old('email') }}" 
                        class="form-input" 
                        required 
                        autocomplete="email" 
                    />
                </div>
                <button type="submit" class="w-full btn-primary">Send Reset Link</button>
            </form>
            <p class="text-center text-sm text-gray-700 mt-6"><a href="{{ route('login') }}" class="text-primary hover:underline">Back to Login</a></p>
        </div>
    </div>
    </div>
@endsection


