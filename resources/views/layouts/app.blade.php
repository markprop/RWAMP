<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="font-montserrat font-roboto font-mono">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'RWAMP - The Currency of Real Estate Investments' }}</title>
    <meta name="description" content="{{ $description ?? 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia. Powered by Mark Properties.' }}">
    <meta name="keywords" content="{{ $keywords ?? 'RWAMP, real estate, token, investment, Dubai, Pakistan, Saudi Arabia, Mark Properties' }}">
    <meta name="author" content="Mark Properties">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/logo.jpeg') }}?v=2" type="image/jpeg" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.jpeg') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $ogTitle ?? 'RWAMP - The Currency of Real Estate Investments' }}">
    <meta property="og:description" content="{{ $ogDescription ?? 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.' }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/logo.jpeg') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $twitterTitle ?? 'RWAMP - The Currency of Real Estate Investments' }}">
    <meta name="twitter:description" content="{{ $twitterDescription ?? 'RWAMP is the official token for investing in real estate projects across Dubai, Pakistan, and Saudi Arabia.' }}">
    <meta name="twitter:image" content="{{ $twitterImage ?? asset('images/logo.jpeg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@400;500;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css'])

    <!-- Google Analytics -->
    @if(config('app.google_analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.google_analytics_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('app.google_analytics_id') }}');
    </script>
    @endif

    <!-- Meta Pixel -->
    @if(config('app.meta_pixel_id'))
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ config('app.meta_pixel_id') }}');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ config('app.meta_pixel_id') }}&ev=PageView&noscript=1"
        />
    </noscript>
    @endif

    @stack('head')
</head>
<body class="font-roboto antialiased" x-data>
    @yield('content')

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
