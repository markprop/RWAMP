@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <h1 class="text-2xl font-montserrat font-bold mb-4 text-center">Reset Password</h1>
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input name="password" type="password" class="form-input" required autocomplete="new-password" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="form-input" required autocomplete="new-password" />
                </div>
                <button type="submit" class="w-full btn-primary">Update Password</button>
            </form>
        </div>
    </div>
    </div>
@endsection

