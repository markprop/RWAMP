@extends('layouts.app')

@section('title', 'Page Not Found')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center px-4">
        <h1 class="text-6xl md:text-7xl font-extrabold text-gray-800 tracking-tight">404</h1>
        <p class="mt-4 text-xl md:text-2xl text-gray-600">The page you’re looking for doesn’t exist.</p>
        <p class="mt-2 text-sm text-gray-500">
            It may have been moved, renamed, or is temporarily unavailable.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="px-6 py-3 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 transition">
                ← Back to Home
            </a>
            @auth
                <a href="{{ route('dashboard.investor') }}" class="px-6 py-3 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-black transition">
                    Go to Dashboard
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection

