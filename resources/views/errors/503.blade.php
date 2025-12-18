@extends('layouts.app')

@section('title', 'Service Temporarily Unavailable')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center px-4">
        <div class="w-16 h-16 mx-auto mb-6 animate-spin rounded-full border-4 border-blue-500 border-t-transparent"></div>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900">We’re updating RWAMP</h2>
        <p class="mt-3 text-base md:text-lg text-gray-600">
            Our systems are temporarily unavailable while we complete an update. Please try again in a few moments.
        </p>
        <button
            type="button"
            onclick="location.reload()"
            class="mt-6 px-6 py-3 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 transition"
        >
            Refresh now
        </button>
    </div>
</div>
@endsection

