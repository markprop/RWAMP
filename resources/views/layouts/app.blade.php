<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="font-montserrat font-roboto font-mono">
<head>
    {{-- Meta Pixel --}}
    @if(config('app.env') === 'production' && config('analytics.meta_pixel_id'))
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ config('analytics.meta_pixel_id') }}');
    fbq('track', 'PageView');
    </script>
    <noscript>
      <img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ config('analytics.meta_pixel_id') }}&ev=PageView&noscript=1"
      />
    </noscript>
    @endif

    {{-- Google Analytics 4 --}}
    @if(config('app.env') === 'production' && config('analytics.google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('analytics.google_analytics_id') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ config('analytics.google_analytics_id') }}');
    </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        use App\Helpers\PriceHelper;
        $usdPkr = PriceHelper::getUsdToPkrRate();
        $aedPkr = PriceHelper::getAedToPkrRate();
    @endphp
    <meta name="exchange-rate-usd-pkr" content="{{ $usdPkr }}">
    <meta name="exchange-rate-aed-pkr" content="{{ $aedPkr }}">

    <title>{{ $title ?? 'RWAMP – The Currency of Real Estate Investments' }}</title>
    <meta name="description" content="{{ $description ?? 'Invest in Dubai, Pakistan & Saudi Arabia real estate with RWAMP tokens. Secure, transparent, and globally accessible.' }}">
    <meta name="keywords" content="{{ $keywords ?? 'RWAMP, real estate, token, investment, Dubai, Pakistan, Saudi Arabia' }}">
    <meta name="author" content="RWAMP">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index,follow">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/logo.png') }}?v=2" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $ogTitle ?? ($title ?? 'RWAMP') }}">
    <meta property="og:description" content="{{ $ogDescription ?? ($description ?? 'RWAMP – Real estate investment token.') }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/logo.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="RWAMP">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@rwamp">
    <meta name="twitter:title" content="{{ $twitterTitle ?? ($title ?? 'RWAMP') }}">
    <meta name="twitter:description" content="{{ $twitterDescription ?? ($description ?? 'RWAMP – Real estate investment token.') }}">
    <meta name="twitter:image" content="{{ $twitterImage ?? asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;500;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- Intl Tel Input CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/css/intlTelInput.css">

    <!-- Intl Tel Input JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/intlTelInput.min.js" defer></script>
    
    <!-- Phone Input Initialization -->
    <script src="{{ asset('js/phone-input.js') }}" defer></script>

    <!-- Structured Data: Organization -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "RWAMP",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/logo.png') }}",
      "sameAs": []
    }
    </script>


    @stack('head')
</head>
<body class="font-roboto antialiased" data-user-role="{{ auth()->check() ? auth()->user()->role : 'guest' }}">
    @php
        $tabId = request()->cookie('tab_session_id');
    @endphp

    @if(auth()->check() && $tabId)
        <div class="w-full bg-gray-900 text-gray-300 text-xs px-4 py-1 flex items-center justify-end gap-2">
            <span class="opacity-75">
                Tab: {{ substr($tabId, 0, 8) }}
            </span>
            <span class="opacity-75">&bull;</span>
            <span class="font-semibold">
                {{ auth()->user()->name }}
            </span>
        </div>
    @endif

    <!-- RWAMP Contract Address Banner -->
    @include('components.contract-address-banner')

    @php
        // Check if current route is a dashboard page (has sidebar, no navbar)
        // Pages with sidebars should not show navbar
        $currentRoute = request()->route();
        $routeName = $currentRoute ? $currentRoute->getName() : null;
        
        $isDashboardPage = request()->routeIs('dashboard.investor') 
            || request()->routeIs('dashboard.admin') 
            || request()->routeIs('dashboard.reseller')
            || request()->routeIs('dashboard.admin.*') 
            || request()->routeIs('dashboard.reseller.*')
            || request()->routeIs('dashboard.investor.*')
            || request()->routeIs('user-history')
            || request()->routeIs('user-withdrawals')
            || request()->routeIs('buy.from.reseller')
            || request()->routeIs('buy.from.reseller.*')
            || request()->routeIs('reseller.*')
            || request()->routeIs('admin.*')
            || request()->routeIs('game.*')
            || request()->routeIs('profile.*')
            || request()->routeIs('user.history')
            || ($routeName && str_starts_with($routeName, 'dashboard.'))
            || ($routeName && str_starts_with($routeName, 'reseller.'))
            || ($routeName && str_starts_with($routeName, 'admin.'))
            || (auth()->check() && $routeName && in_array($routeName, ['game.select', 'game.index']));
    @endphp
    
    @if(!$isDashboardPage)
        @include('components.navbar')
    @endif
    
    <main class="{{ $isDashboardPage ? 'pt-7' : 'pt-28' }}" style="{{ $isDashboardPage ? 'padding-top: 28px;' : 'padding-top: calc(28px + 4rem);' }}">
        @yield('content')
    </main>

    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Tawk.to Live Chat Widget -->
    @include('components.tawk-to')
</body>
</html>
