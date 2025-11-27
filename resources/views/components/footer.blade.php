@php
$socialLinks = [
    [
        'name' => 'Facebook',
        'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'url' => 'https://www.facebook.com/rwampofficial',
        'class' => 'social-facebook'
    ],
    [
        'name' => 'Instagram',
        'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        'url' => 'https://www.instagram.com/rwampofficial',
        'class' => 'social-instagram'
    ],
    [
        'name' => 'Twitter/X',
        'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'url' => 'https://x.com/rwampofficial',
        'class' => 'social-twitter'
    ],
    [
        'name' => 'Telegram',
        'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        'url' => 'https://t.me/rwamptoken',
        'class' => 'social-telegram'
    ]
];

$quickLinks = [
    ['name' => 'About RWAMP', 'href' => route('home') . '#about'],
    ['name' => 'Why Invest', 'href' => route('home') . '#why-invest'],
    ['name' => 'Become a Partner', 'href' => route('become.partner')],
    ['name' => 'Roadmap', 'href' => route('home') . '#roadmap'],
    ['name' => 'Contact', 'href' => route('home') . '#signup']
];
@endphp

<footer class="bg-black text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Logo and Description -->
            <div class="lg:col-span-2 animate-fadeInUp">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                        <img 
                            src="{{ asset('images/logo.jpeg') }}" 
                            alt="RWAMP Logo" 
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div>
                        <h3 class="text-2xl font-montserrat font-bold">RWAMP</h3>
                        <p class="text-sm text-gray-400">The Currency of Real Estate</p>
                    </div>
                </div>
                
                <p class="text-gray-300 mb-6 leading-relaxed">
                    RWAMP is the official token for investing in real estate projects across Dubai, 
                    Pakistan, and Saudi Arabia.
                </p>
                
                
            </div>
            
            <!-- Quick Links -->
            <div class="animate-fadeInUp" style="animation-delay: 0.1s">
                <h4 class="text-lg font-montserrat font-bold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    @foreach($quickLinks as $link)
                    <li>
                        <a
                            href="{{ $link['href'] }}"
                            class="text-gray-300 hover:text-accent transition-colors duration-300"
                        >
                            {{ $link['name'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            
            <!-- Social Links -->
            <div class="animate-fadeInUp" style="animation-delay: 0.2s">
                <h4 class="text-lg font-montserrat font-bold mb-4">🌐 Follow Us</h4>
                <div class="flex flex-wrap gap-3 mb-6">
                    @foreach($socialLinks as $social)
                    <a
                        href="{{ $social['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="social-link {{ $social['class'] }} w-11 h-11 rounded-full flex items-center justify-center transition-all duration-300 relative overflow-hidden border-2 border-transparent"
                        aria-label="{{ $social['name'] }}"
                    >
                        {!! $social['icon'] !!}
                    </a>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    <h5 class="font-montserrat font-bold mb-2">📬 Contact Info</h5>
                    <div class="space-y-1 text-sm text-gray-300">
                        <p>📧 info@rwamp.com</p>
                        <p>📱 +92 300 1234567</p>
                        <p>🌍 Pakistan, Dubai, UAE</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 mt-8 pt-8 animate-fadeInUp" style="animation-delay: 0.3s">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="text-sm text-gray-400">
                    © 2024 RWAMP. All rights reserved.
                </div>
                
                <div class="flex space-x-6 text-sm">
                    <a href="{{ route('privacy.policy') }}" class="text-gray-400 hover:text-accent transition-colors duration-300">Privacy Policy</a>
                    <a href="{{ route('terms.of.service') }}" class="text-gray-400 hover:text-accent transition-colors duration-300">Terms of Service</a>
                    <a href="{{ route('disclaimer') }}" class="text-gray-400 hover:text-accent transition-colors duration-300">Disclaimer</a>
                </div>
                
                <button
                    onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="w-10 h-10 bg-primary rounded-full flex items-center justify-center hover:bg-red-700 transition-all duration-300 hover:scale-110"
                    aria-label="Scroll to top"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</footer>
