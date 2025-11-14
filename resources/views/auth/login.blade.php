@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP" class="w-16 h-16 mx-auto rounded-full mb-3">
                <h1 class="text-2xl font-montserrat font-bold">Welcome back</h1>
                <p class="text-gray-600">Login to continue</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4" novalidate>
                @csrf
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('login-role').value='investor'; this.classList.add('border-primary','text-primary'); document.getElementById('role-reseller').classList.remove('border-primary','text-primary'); document.getElementById('role-admin').classList.remove('border-primary','text-primary');" id="role-investor" class="px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-700">Investor</button>
                    <button type="button" onclick="document.getElementById('login-role').value='reseller'; this.classList.add('border-primary','text-primary'); document.getElementById('role-investor').classList.remove('border-primary','text-primary'); document.getElementById('role-admin').classList.remove('border-primary','text-primary');" id="role-reseller" class="px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-700">Reseller</button>
                    <button type="button" onclick="document.getElementById('login-role').value='admin'; this.classList.add('border-primary','text-primary'); document.getElementById('role-investor').classList.remove('border-primary','text-primary'); document.getElementById('role-reseller').classList.remove('border-primary','text-primary');" id="role-admin" class="px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-700">Admin</button>
                </div>
                <input type="hidden" name="role" id="login-role" value="">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" class="form-input" required autocomplete="email" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input name="password" type="password" class="form-input" required autocomplete="current-password" />
                </div>
                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="remember" class="rounded border-gray-300" /> Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">Forgot Password?</a>
                </div>
                <button type="submit" class="w-full btn-primary">Login</button>
            </form>

            <p class="text-center text-sm text-gray-700 mt-6">Don't have an account?
                <a href="{{ route('register') }}" class="text-primary hover:underline">Sign up</a>
            </p>
        </div>
    </div>
</div>
@endsection


