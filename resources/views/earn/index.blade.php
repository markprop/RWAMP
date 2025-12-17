@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @if(auth()->user()->role === 'admin')
        @include('components.admin-sidebar')
    @elseif(auth()->user()->role === 'reseller')
        @include('components.reseller-sidebar')
    @else
        @include('components.investor-sidebar')
    @endif
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Earn RWAMP Coin</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Discover ways to earn RWAMP tokens</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-6xl mx-auto">
                <!-- Info Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-900 mb-2">About Earning RWAMP Coins</h4>
                            <p class="text-sm text-blue-800">
                                There are various ways to earn RWAMP tokens. Check back soon for more earning opportunities and rewards programs.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Earning Methods Grid -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Coming Soon Card -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <div class="bg-gradient-to-br from-primary to-red-600 rounded-lg p-3">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Coming Soon</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Earning Methods</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            New earning opportunities will be available soon. Stay tuned for updates!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
