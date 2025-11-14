@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-black">
    <!-- Back Button -->
    <div class="max-w-7xl mx-auto px-4 pt-8 pb-4">
        <a 
            href="{{ route('home') }}" 
            class="inline-flex items-center text-white hover:text-accent transition-colors duration-300 group"
        >
            <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-medium">Back to Home</span>
        </a>
    </div>
    
    @include('components.reseller-section')
    
    @include('components.footer')
</div>
@endsection

