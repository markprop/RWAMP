<!-- Investor Dashboard Sidebar -->
<style>
    /* Custom scrollbar for sidebar */
    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }
    .sidebar-nav::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    .sidebar-nav::-webkit-scrollbar-thumb {
        background: #4a5568;
        border-radius: 3px;
    }
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: #5a6578;
    }
</style>
<div x-data="{ mobileOpen: false }">
    <aside class="fixed left-0 w-64 bg-[#121212] text-white z-40 flex flex-col shadow-2xl transform -translate-x-full md:translate-x-0 transition-transform duration-300" 
           style="top: 28px; bottom: 0;"
           :class='mobileOpen ? "translate-x-0" : "-translate-x-full md:translate-x-0"'>
    <!-- Logo / Header Section -->
    <div class="p-5 border-b border-gray-800 relative z-30 sidebar-header" style="padding-top: 1.5rem;">
        <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
            <div class="relative coin-ring">
                <img src="{{ asset('images/logo.png') }}" alt="RWAMP" class="h-10 w-10 rounded-full object-cover rwamp-coin-logo">
            </div>
            <span class="text-xl font-bold text-white group-hover:text-primary transition-colors duration-200">RWAMP</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4 relative z-20 sidebar-nav pt-3">
        <div class="px-3 space-y-1">
            <!-- Dashboard -->
            <a href="{{ route('dashboard.investor') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('dashboard.investor') ? 'bg-primary text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium text-sm">Dashboard</span>
            </a>

            <!-- Purchase History -->
            <a href="{{ route('user.history') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('user.history') ? 'bg-primary text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <span class="font-medium text-sm">Purchase History</span>
            </a>

            <!-- Buy Coins -->
            <button onclick="window.dispatchEvent(new CustomEvent('open-purchase-modal'))" 
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 text-gray-300 hover:bg-gray-800 hover:text-white text-left btn-small">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium text-sm">Buy Coins</span>
            </button>

            <!-- Buy From Reseller -->
            <button onclick="window.dispatchEvent(new CustomEvent('open-buy-from-reseller-modal'))" 
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 text-gray-300 hover:bg-gray-800 hover:text-white text-left btn-small">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="font-medium text-sm">Buy From Reseller</span>
            </button>

            @if(auth()->user()->kyc_status === 'approved' && auth()->user()->token_balance > 0)
            <!-- Play Game -->
            <a href="{{ route('game.select') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('game.*') ? 'bg-purple-600 text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium text-sm">Play Game</span>
            </a>
            @endif

            <!-- Divider -->
            <div class="px-4 py-3">
                <div class="border-t border-gray-700"></div>
            </div>

            <!-- Profile -->
            <a href="{{ route('profile.show') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('profile.*') ? 'bg-primary text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="font-medium text-sm">Profile</span>
            </a>

            <!-- Home -->
            <a href="{{ route('home') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('home') ? 'bg-primary text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="font-medium text-sm">Home</span>
            </a>

            <!-- About -->
            <a href="{{ route('about') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('about') ? 'bg-primary text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-medium text-sm">About</span>
            </a>

            <!-- Contact Us -->
            <a href="{{ route('contact') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 btn-small {{ request()->routeIs('contact') ? 'bg-primary text-white shadow-lg' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium text-sm">Contact Us</span>
            </a>

            <!-- WhitePaper -->
            <a href="{{ asset('Whitepaper-Design.pdf') }}" 
               download="RWAMP-Whitepaper.pdf"
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 text-gray-300 hover:bg-gray-800 hover:text-white btn-small">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="font-medium text-sm">WhitePaper</span>
            </a>
        </div>
    </nav>

    <!-- Logout Button -->
    <div class="p-4 border-t border-gray-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-400 hover:bg-red-900/20 hover:text-red-300 transition-all duration-200 btn-small">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="font-medium text-sm">Logout</span>
            </button>
        </form>
    </div>
    </aside>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="mobileOpen" 
         x-cloak
         @click="mobileOpen = false"
         class="fixed inset-0 bg-black/50 z-20 md:hidden"
         style="display: none;">
    </div>

    <!-- Mobile Menu Toggle Button -->
    <button @click="mobileOpen = !mobileOpen"
            class="fixed top-4 left-4 z-50 md:hidden bg-[#121212] text-white p-2 rounded-lg shadow-lg">
        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

