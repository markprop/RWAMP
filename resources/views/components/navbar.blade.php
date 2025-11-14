<nav class="bg-white/90 backdrop-blur-md shadow-lg fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="RWAMP" class="h-8 w-8 rounded-full">
                    <span class="ml-2 text-xl font-bold text-gray-900">RWAMP</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-8">
                    <a href="{{ route('home') }}" 
                       class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('home') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('about') }}" 
                       class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('about') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        About
                    </a>
                    <a href="{{ route('contact') }}" 
                       class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('contact') ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                        Contact Us
                    </a>
                    <a href="{{ asset('Whitepaper-Design.pdf') }}" 
                       download="RWAMP-Whitepaper.pdf"
                       class="inline-flex items-center gap-1.5 bg-gradient-to-r from-primary to-red-600 hover:from-primary/90 hover:to-red-600/90 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        WhitePaper
                    </a>
                </div>
            </div>

            <!-- Auth Buttons -->
            <div class="hidden md:block">
                @auth
                    @php
                        $dashboardRoute = auth()->user()->role === 'admin'
                            ? route('dashboard.admin')
                            : (auth()->user()->role === 'reseller' ? route('dashboard.reseller') : route('dashboard.investor'));
                    @endphp
                    <div class="flex items-center space-x-4">
                        @if(auth()->user()->role === 'admin')
                            @if(auth()->user()->two_factor_secret && auth()->user()->two_factor_confirmed_at)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">2FA On</span>
                            @else
                                <a href="{{ route('admin.2fa.setup') }}" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 hover:bg-yellow-200">Enable 2FA</a>
                            @endif
                        @endif
                        <a href="{{ $dashboardRoute }}"
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200 {{ request()->url() === $dashboardRoute ? 'text-blue-600 border-b-2 border-blue-600' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('profile.show') }}" 
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200">
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                Logout
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" 
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium transition-colors duration-200">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            Sign Up
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" 
                        class="mobile-menu-button bg-gray-100 inline-flex items-center justify-center p-2 rounded-md text-gray-900 hover:text-blue-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                        aria-controls="mobile-menu" 
                        aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- Hamburger icon -->
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="md:hidden hidden mobile-menu" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white/95 backdrop-blur-md">
            <a href="{{ route('home') }}" 
               class="text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium {{ request()->routeIs('home') ? 'text-blue-600 bg-blue-50' : '' }}">
                Home
            </a>
            <a href="{{ route('about') }}" 
               class="text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium {{ request()->routeIs('about') ? 'text-blue-600 bg-blue-50' : '' }}">
                About
            </a>
            <a href="{{ route('contact') }}" 
               class="text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium {{ request()->routeIs('contact') ? 'text-blue-600 bg-blue-50' : '' }}">
                Contact Us
            </a>
            <a href="{{ asset('Whitepaper-Design.pdf') }}" 
               download="RWAMP-Whitepaper.pdf"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-red-600 hover:from-primary/90 hover:to-red-600/90 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg mx-3 my-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                WhitePaper
            </a>
            
            @auth
                @php
                    $dashboardRoute = auth()->user()->role === 'admin'
                        ? route('dashboard.admin')
                        : (auth()->user()->role === 'reseller' ? route('dashboard.reseller') : route('dashboard.investor'));
                @endphp
                <a href="{{ $dashboardRoute }}" 
                   class="text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium {{ request()->url() === $dashboardRoute ? 'text-blue-600 bg-blue-50' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('profile.show') }}" 
                   class="text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium">
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full text-left text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" 
                   class="text-gray-900 hover:text-blue-600 block px-3 py-2 text-base font-medium">
                    Login
                </a>
                <a href="{{ route('register') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white block px-3 py-2 text-base font-medium rounded-md">
                    Sign Up
                </a>
            @endauth
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
