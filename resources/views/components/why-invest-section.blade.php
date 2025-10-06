@php
$features = [
    [
        'icon' => '🔑',
        'title' => 'Access to Exclusive Projects',
        'description' => 'Get early access to premium real estate projects that are not available to the general public.',
        'color' => 'from-primary to-red-600'
    ],
    [
        'icon' => '📈',
        'title' => 'Token Value Growth',
        'description' => 'Benefit from the appreciation of RWAMP tokens as more projects are launched and demand increases.',
        'color' => 'from-accent to-yellow-500'
    ],
    [
        'icon' => '🏢',
        'title' => 'Backed by Mark Properties',
        'description' => 'All investments are secured by the reputation and expertise of Mark Properties in real estate.',
        'color' => 'from-success to-green-600'
    ],
    [
        'icon' => '💵',
        'title' => 'Earn Dividends & Trading Profits',
        'description' => 'Receive regular dividends from project profits and capitalize on token trading opportunities.',
        'color' => 'from-blue-500 to-blue-600'
    ]
];
@endphp

<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 animate-fadeInUp">
            <h2 class="text-4xl md:text-5xl font-montserrat font-bold text-gray-900 mb-6">
                Why Invest in <span class="text-primary">RWAMP</span>?
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Discover the unique advantages that make RWAMP the smart choice for real estate investment
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($features as $index => $feature)
            <div class="group animate-fadeInUp" style="animation-delay: {{ $index * 0.1 }}s">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 card-hover h-full">
                    <div class="text-center">
                        <!-- Icon with gradient background -->
                        <div class="w-20 h-20 bg-gradient-to-br {{ $feature['color'] }} rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <span class="text-4xl">{{ $feature['icon'] }}</span>
                        </div>
                        
                        <h3 class="text-xl font-montserrat font-bold text-gray-900 mb-4">
                            {{ $feature['title'] }}
                        </h3>
                        
                        <p class="text-gray-600 leading-relaxed">
                            {{ $feature['description'] }}
                        </p>
                    </div>
                    
                    <!-- Hover effect line -->
                    <div class="mt-6 h-1 bg-gradient-to-r {{ $feature['color'] }} rounded-full transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Bottom CTA -->
        <div class="text-center mt-16 animate-fadeInUp">
            <div class="bg-gradient-to-r from-primary to-red-600 rounded-2xl p-8 text-white">
                <h3 class="text-2xl md:text-3xl font-montserrat font-bold mb-4">
                    Ready to Start Your Investment Journey?
                </h3>
                <p class="text-lg mb-6 opacity-90">
                    Join thousands of investors who trust RWAMP for their real estate investments
                </p>
                <button 
                    onclick="document.getElementById('signup').scrollIntoView({ behavior: 'smooth' })"
                    class="bg-white text-primary px-8 py-4 rounded-lg font-montserrat font-bold text-lg hover:bg-gray-100 transition-all duration-300 hover:scale-105 hover:shadow-lg"
                >
                    Get Started Now
                </button>
            </div>
        </div>
    </div>
</section>
