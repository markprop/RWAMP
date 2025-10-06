<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left side - Illustration -->
            <div class="relative animate-fadeInUp">
                <div class="relative bg-gradient-to-br from-primary/10 to-accent/10 rounded-2xl p-8">
                    <!-- Token illustration -->
                    <div class="flex justify-center items-center mb-8">
                        <div class="relative">
                            <div class="w-32 h-32 rounded-full overflow-hidden shadow-2xl">
                                <img 
                                    src="{{ asset('images/logo.jpeg') }}" 
                                    alt="RWAMP Logo" 
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-success rounded-full flex items-center justify-center">
                                <span class="text-white text-sm">✓</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property icons -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-primary/20 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <span class="text-2xl">🏢</span>
                            </div>
                            <p class="text-sm font-montserrat font-semibold text-gray-700">Dubai</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-accent/20 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <span class="text-2xl">🏗️</span>
                            </div>
                            <p class="text-sm font-montserrat font-semibold text-gray-700">Pakistan</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-success/20 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <span class="text-2xl">🏘️</span>
                            </div>
                            <p class="text-sm font-montserrat font-semibold text-gray-700">Saudi Arabia</p>
                        </div>
                    </div>
                    
                    <!-- Connection lines -->
                    <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-primary via-accent to-success -z-10"></div>
                </div>
            </div>
            
            <!-- Right side - Text content -->
            <div class="space-y-6 animate-fadeInUp">
                <h2 class="text-4xl md:text-5xl font-montserrat font-bold text-gray-900 mb-6">
                    About <span class="text-primary">RWAMP</span>
                </h2>
                
                <p class="text-lg text-gray-700 leading-relaxed mb-6">
                    RWAMP is the official token for investing in real estate projects across 
                    <span class="font-semibold text-primary"> Dubai</span>, 
                    <span class="font-semibold text-accent"> Pakistan</span>, and 
                    <span class="font-semibold text-success"> Saudi Arabia</span>. 
                    All projects are powered by Mark Properties.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 bg-primary rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-white text-sm">✓</span>
                        </div>
                        <p class="text-gray-700">
                            <span class="font-semibold">Verified Projects:</span> All real estate investments are backed by verified, high-potential properties.
                        </p>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 bg-accent rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-black text-sm">✓</span>
                        </div>
                        <p class="text-gray-700">
                            <span class="font-semibold">Mark Properties Backing:</span> Industry expertise and proven track record in real estate development.
                        </p>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-6 h-6 bg-success rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <span class="text-white text-sm">✓</span>
                        </div>
                        <p class="text-gray-700">
                            <span class="font-semibold">Multi-Market Access:</span> Diversified investment opportunities across three major markets.
                        </p>
                    </div>
                </div>
                
                <div class="pt-6">
                    <div class="bg-gradient-to-r from-primary/10 to-accent/10 rounded-lg p-6">
                        <h3 class="font-montserrat font-bold text-xl text-gray-900 mb-2">
                            Powered by Mark Properties
                        </h3>
                        <p class="text-gray-700">
                            Your trusted partner in real estate investment with years of experience 
                            and a proven track record of successful projects.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
