@php
$phases = [
    [
        'phase' => 'Phase 1',
        'title' => 'Presale & Brokers',
        'status' => 'current',
        'description' => 'Launch presale program and establish broker network',
        'features' => [
            'Token presale launch',
            'Broker recruitment program',
            'Marketing campaign initiation',
            'Community building',
            'Partnership agreements'
        ],
        'color' => 'from-primary to-red-600',
        'icon' => '🚀'
    ],
    [
        'phase' => 'Phase 2',
        'title' => 'First Project Launch',
        'status' => 'upcoming',
        'description' => 'Launch the first real estate project in Dubai',
        'features' => [
            'Dubai project selection',
            'Property acquisition',
            'Development planning',
            'Investor onboarding',
            'Project execution'
        ],
        'color' => 'from-accent to-yellow-500',
        'icon' => '🏗️'
    ],
    [
        'phase' => 'Phase 3',
        'title' => 'Exchange Listing & Expansion',
        'status' => 'future',
        'description' => 'List on exchanges and expand to Pakistan & Saudi Arabia',
        'features' => [
            'Exchange listing',
            'Pakistan market entry',
            'Saudi Arabia expansion',
            'Advanced trading features',
            'Global partnerships'
        ],
        'color' => 'from-success to-green-600',
        'icon' => '🌍'
    ]
];
@endphp

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16 animate-fadeInUp">
            <h2 class="text-4xl md:text-5xl font-montserrat font-bold text-gray-900 mb-6">
                Our <span class="text-primary">Roadmap</span>
            </h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                A clear path to success with defined milestones and objectives
            </p>
        </div>
        
        <!-- Desktop Timeline -->
        <div class="hidden lg:block">
            <div class="relative">
                <!-- Timeline line -->
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 transform -translate-y-1/2"></div>
                
                <div class="grid grid-cols-3 gap-8">
                    @foreach($phases as $index => $phase)
                    <div class="relative animate-fadeInUp" style="animation-delay: {{ $index * 0.2 }}s">
                        <!-- Timeline dot -->
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8 bg-gradient-to-r {{ $phase['color'] }} rounded-full z-10 flex items-center justify-center">
                            <span class="text-white text-sm font-bold">{{ $index + 1 }}</span>
                        </div>
                        
                        <!-- Phase card -->
                        <div class="bg-white rounded-2xl p-8 shadow-lg border-t-4 bg-gradient-to-br {{ $phase['color'] }} border-opacity-20 {{ $phase['status'] === 'current' ? 'ring-4 ring-primary ring-opacity-30' : '' }}">
                            <div class="text-center mb-6">
                                <div class="text-4xl mb-4">{{ $phase['icon'] }}</div>
                                <div class="inline-block px-3 py-1 rounded-full text-sm font-montserrat font-bold {{ $phase['status'] === 'current' ? 'bg-primary text-white' : ($phase['status'] === 'upcoming' ? 'bg-accent text-black' : 'bg-gray-300 text-gray-600') }}">
                                    {{ $phase['phase'] }}
                                </div>
                                <h3 class="text-xl font-montserrat font-bold text-gray-900 mt-4">
                                    {{ $phase['title'] }}
                                </h3>
                                <p class="text-gray-600 mt-2">
                                    {{ $phase['description'] }}
                                </p>
                            </div>
                            
                            <ul class="space-y-2">
                                @foreach($phase['features'] as $feature)
                                <li class="flex items-start space-x-2">
                                    <div class="w-2 h-2 bg-gradient-to-r {{ $phase['color'] }} rounded-full flex-shrink-0 mt-2"></div>
                                    <span class="text-sm text-gray-700">{{ $feature }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Mobile Timeline -->
        <div class="lg:hidden space-y-8">
            @foreach($phases as $index => $phase)
            <div class="relative animate-fadeInUp" style="animation-delay: {{ $index * 0.2 }}s">
                <!-- Timeline line for mobile -->
                @if($index < count($phases) - 1)
                <div class="absolute left-8 top-20 bottom-0 w-0.5 bg-gray-200"></div>
                @endif
                
                <div class="flex items-start space-x-6">
                    <!-- Timeline dot -->
                    <div class="w-16 h-16 bg-gradient-to-r {{ $phase['color'] }} rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xl font-bold">{{ $index + 1 }}</span>
                    </div>
                    
                    <!-- Phase card -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg border-t-4 bg-gradient-to-br {{ $phase['color'] }} border-opacity-20 flex-1 {{ $phase['status'] === 'current' ? 'ring-4 ring-primary ring-opacity-30' : '' }}">
                        <div class="mb-4">
                            <div class="text-3xl mb-2">{{ $phase['icon'] }}</div>
                            <div class="inline-block px-3 py-1 rounded-full text-sm font-montserrat font-bold {{ $phase['status'] === 'current' ? 'bg-primary text-white' : ($phase['status'] === 'upcoming' ? 'bg-accent text-black' : 'bg-gray-300 text-gray-600') }}">
                                {{ $phase['phase'] }}
                            </div>
                            <h3 class="text-lg font-montserrat font-bold text-gray-900 mt-3">
                                {{ $phase['title'] }}
                            </h3>
                            <p class="text-gray-600 text-sm mt-2">
                                {{ $phase['description'] }}
                            </p>
                        </div>
                        
                        <ul class="space-y-2">
                            @foreach($phase['features'] as $feature)
                            <li class="flex items-start space-x-2">
                                <div class="w-2 h-2 bg-gradient-to-r {{ $phase['color'] }} rounded-full flex-shrink-0 mt-2"></div>
                                <span class="text-xs text-gray-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Bottom CTA -->
        <div class="text-center mt-16 animate-fadeInUp">
            <div class="bg-gradient-to-r from-gray-900 to-black rounded-2xl p-8 text-white">
                <h3 class="text-2xl md:text-3xl font-montserrat font-bold mb-4">
                    Be Part of Our Journey
                </h3>
                <p class="text-lg mb-6 opacity-90">
                    Join us as we revolutionize real estate investment through blockchain technology
                </p>
                <button 
                    onclick="document.getElementById('signup').scrollIntoView({ behavior: 'smooth' })"
                    class="bg-primary text-white px-8 py-4 rounded-lg font-montserrat font-bold text-lg hover:bg-red-700 transition-all duration-300 hover:scale-105 hover:shadow-lg"
                >
                    Join the Community
                </button>
            </div>
        </div>
    </div>
</section>
