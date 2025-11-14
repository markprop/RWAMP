@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-black to-secondary text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">About RWAMP</h1>
                <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto">
                    The Currency of Real Estate Investments
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Content -->
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Revolutionizing Real Estate Investment</h2>
                <p class="text-lg text-gray-600 mb-6">
                    RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia. 
                    Powered by Mark Properties, we're bringing blockchain technology to the traditional real estate market.
                </p>
                <p class="text-lg text-gray-600 mb-6">
                    Our platform enables investors to participate in high-value real estate projects with lower barriers to entry, 
                    transparent transactions, and global accessibility.
                </p>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">Transparent and secure transactions</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">Global accessibility to premium real estate</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700">Lower investment barriers</span>
                    </div>
                </div>
            </div>

            <!-- Image -->
            <div class="relative">
                <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP Logo" class="w-full h-96 object-cover rounded-lg shadow-xl">
                <div class="absolute inset-0 bg-primary opacity-20 rounded-lg"></div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="text-4xl font-bold text-primary mb-2">3</div>
                <div class="text-gray-600">Countries</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-primary mb-2">$50M+</div>
                <div class="text-gray-600">Total Value</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-primary mb-2">1000+</div>
                <div class="text-gray-600">Investors</div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="mt-20">
            <h3 class="text-2xl font-montserrat font-bold mb-6">Our Team</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ([1,2,3,4] as $i)
                    <div class="bg-white rounded-xl shadow-lg p-6 text-center card-hover animate-fadeInUp">
                        <div class="w-24 h-24 rounded-full bg-gray-200 mx-auto mb-4 flex items-center justify-center">👤</div>
                        <div class="font-semibold">Team Member {{ $i }}</div>
                        <div class="text-sm text-gray-600">Title</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Timeline -->
        <div class="mt-20">
            <h3 class="text-2xl font-montserrat font-bold mb-6">Milestones</h3>
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="w-3 h-3 rounded-full bg-primary mt-2"></div>
                    <div>
                        <div class="font-semibold">2024 Q4 - Token Launch</div>
                        <div class="text-gray-600 text-sm">RWAMP token launched with strong community interest.</div>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-3 h-3 rounded-full bg-primary mt-2"></div>
                    <div>
                        <div class="font-semibold">2025 Q1 - Reseller Program</div>
                        <div class="text-gray-600 text-sm">Partner network established across three countries.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
