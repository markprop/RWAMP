@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Purchase RWAMP Tokens</h1>
            <p class="text-white/80 mt-2">Login required to access the crypto purchase flow.</p>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 py-12">
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-lg mb-6">
            <div class="font-semibold">Please login to continue</div>
            <div class="text-sm">You must be logged in to purchase tokens. Create an account if you don’t have one.</div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('login') }}" class="btn-primary">Login</a>
            <a href="{{ route('register') }}" class="btn-secondary">Sign Up</a>
        </div>
    </div>
</div>
@endsection


