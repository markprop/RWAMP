@extends('layouts.app')

@php
    $user = auth()->user();
    $hasTradingPin = $user->hasGamePin('trading');
    $hasFopiPin = $user->hasGamePin('fopi');
    $hasPin = $hasTradingPin; // For backward compatibility
    $isInGame = $user->is_in_game ?? false;
    $alpineComponent = $user->role === 'reseller' 
        ? 'gameDashboard(' . ($isInGame ? 'true' : 'false') . ', ' . ($hasTradingPin ? 'true' : 'false') . ', ' . ($hasFopiPin ? 'true' : 'false') . ')' 
        : 'investorDashboard(' . ($isInGame ? 'true' : 'false') . ', ' . ($hasTradingPin ? 'true' : 'false') . ', ' . ($hasFopiPin ? 'true' : 'false') . ')';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50" 
     x-data="{{ $alpineComponent }}" 
     x-init="gameType = 'trading'"
     x-cloak>
    @include('components.game-modals')
    
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
                        <p class="text-gray-500 text-sm mt-1.5">Choose a game to earn RWAMP tokens</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-6xl mx-auto">
                <!-- Game Cards Grid -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Game 1: Trading Game -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="bg-gradient-to-br from-primary to-red-600 rounded-lg p-3">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Trading Game</h3>
                                    <p class="text-sm text-gray-500">BTC-Linked Trading Simulation</p>
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
                        @endphp
                        @if($hasActiveSession)
                            <a href="{{ route('game.index') }}" 
                               class="block w-full bg-gradient-to-r from-primary to-red-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Continue Game
                            </a>
                        @else
                            <button @click="showGameWarning = true" 
                                    type="button"
                                    class="block w-full bg-gradient-to-r from-primary to-red-600 text-white text-center py-3 px-6 rounded-lg font-semibold hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Play Now
                            </button>
                        @endif
                    </div>

                    <!-- Game 2: FOPI Game -->
                    @php
                        $fopiActiveSession = auth()->user()->gameSessions()
                            ->where('type', 'fopi')
                            ->where('status', 'active')
                            ->first();
                    @endphp
                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="rounded-lg p-3 flex-shrink-0" style="background: linear-gradient(to bottom right, #a855f7, #db2777);">
                                    <svg class="w-8 h-8" fill="none" stroke="#ffffff" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: #ffffff;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">FOPI Game</h3>
                                    <p class="text-sm text-gray-500">Future of Property Investment</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Unavailable</span>
                        </div>
                        
                        @php
                            $gameSettings = \App\Models\GameSetting::current();
                            $fopiPerRwamp = $gameSettings->fopi_per_rwamp ?? 1000;
                        @endphp
                        <p class="text-gray-600 mb-6">
                            Invest in virtual properties, collect rental yields, and build your real estate empire. 
                            Your RWAMP stake is converted to FOPI game coins (1 RWAMP → {{ number_format($fopiPerRwamp, 0) }} FOPI) for property investments.
                        </p>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: #10b981;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Property investment & trading
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: #10b981;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Rental yield collection
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: #10b981;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Mission & achievement system
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="color: #10b981;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Time-based progression
                            </div>
                        </div>
                        
                        @if($fopiActiveSession)
                            <a href="{{ route('game.fopi.index') }}" 
                               class="block w-full text-white text-center py-3 px-6 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl"
                               style="background: linear-gradient(to right, #9333ea, #db2777);">
                                Continue Game
                            </a>
                        @else
                            <button @click="openFopiGame()" 
                                    type="button"
                                    class="w-full text-white text-center py-3 px-6 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl"
                                    style="background: linear-gradient(to right, #9333ea, #db2777); display: block;">
                                Play Now
                            </button>
                        @endif
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

