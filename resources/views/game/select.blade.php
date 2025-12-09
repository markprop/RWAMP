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
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Select Game</h1>
                        <p class="text-gray-500 text-sm mt-1.5">Choose a game to play</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-6xl mx-auto">
                <!-- Game Cards Grid -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Game 1: BTC-Linked Trading Simulation -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-gradient-to-br from-primary to-red-600 rounded-lg p-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">BTC-Linked Trading</h3>
                                    <p class="text-sm text-gray-500">Trading Simulation</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Available</span>
                        </div>
                        
                        @php
                            $gameSettings = \App\Models\GameSetting::current();
                        @endphp
                        <p class="text-gray-600 mb-6">
                            Experience real-time RWAMP token trading linked to Bitcoin prices. Inside the game, your RWAMP stake is converted to
                            <span class="font-semibold">FOPI</span> in‑game coins
                            (1 RWAMP → {{ number_format($gameSettings->entry_multiplier, 2) }} FOPI) for trading, and on exit your FOPI balance is
                            divided by {{ number_format($gameSettings->exit_divisor, 2) }}
                            @if($gameSettings->exit_fee_rate > 0)
                                and reduced by an exit fee of {{ number_format($gameSettings->exit_fee_rate, 2) }}%
                            @endif
                            before being swapped back to RWAMP.
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Real-time BTC price tracking
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Buy/Sell trading simulation
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Trade history & analytics
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Portfolio tracking
                            </div>
                        </div>
                        
                        @php
                            $user = auth()->user();
                            $hasActiveSession = $user->activeGameSession ? true : false;
                            $dashboardRoute = $user->role === 'reseller' ? route('dashboard.reseller') : route('dashboard.investor');
                        @endphp
                        @if($hasActiveSession)
                            <a href="{{ route('game.index') }}" 
                               class="block w-full bg-gradient-to-r from-primary to-red-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Continue Game
                            </a>
                        @else
                            <a href="{{ $dashboardRoute }}?open=game" 
                               class="block w-full bg-gradient-to-r from-primary to-red-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Play Now
                            </a>
                        @endif
                    </div>

                    <!-- Game 2: Coming Soon -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 opacity-75 relative">
                        <div class="absolute top-4 right-4">
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Coming Soon</span>
                        </div>
                        
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg p-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">New Game</h3>
                                    <p class="text-sm text-gray-500">Under Development</p>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-600 mb-6">
                            We're working on an exciting new game experience. Stay tuned for updates!
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm text-gray-400">
                                <svg class="w-5 h-5 text-gray-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                New features coming soon
                            </div>
                            <div class="flex items-center text-sm text-gray-400">
                                <svg class="w-5 h-5 text-gray-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Enhanced gameplay
                            </div>
                            <div class="flex items-center text-sm text-gray-400">
                                <svg class="w-5 h-5 text-gray-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                More rewards
                            </div>
                        </div>
                        
                        <button 
                            disabled
                            class="block w-full bg-gray-300 text-gray-500 text-center py-3 px-6 rounded-lg font-semibold cursor-not-allowed">
                            Coming Soon
                        </button>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-blue-900 mb-2">About Games</h4>
                            <p class="text-sm text-blue-800">
                                Games allow you to practice trading strategies with simulated balances. Your real token balance is temporarily locked when you enter a game, and results are applied when you exit. Make sure you understand the game mechanics before playing.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

