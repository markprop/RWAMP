<div x-data="presaleModal" x-show="$store.modal.open" x-cloak class="fixed inset-0 z-50">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/80" @click="$store.modal.open=false"></div>

    <!-- Modal -->
    <div class="relative z-10 max-w-2xl mx-auto mt-24 bg-gray-900 text-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-6 md:p-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-montserrat font-bold">Phase 1 Presale</h3>
                <button @click="$store.modal.open=false" class="text-white/70 hover:text-white">✕</button>
            </div>

            <div class="mb-4">
                <div class="text-center bg-accent text-black font-montserrat font-bold rounded-lg py-2">
                    BUY NOW BEFORE PRICE INCREASES 3X
                </div>
            </div>

            <!-- Countdown -->
            <div class="grid grid-cols-4 gap-4 text-center mb-6">
                <div class="bg-black/40 rounded-lg p-4">
                    <div class="text-3xl font-mono font-bold" x-text="timeLeft.days"></div>
                    <div class="text-xs opacity-70">Days</div>
                </div>
                <div class="bg-black/40 rounded-lg p-4">
                    <div class="text-3xl font-mono font-bold" x-text="timeLeft.hours"></div>
                    <div class="text-xs opacity-70">Hours</div>
                </div>
                <div class="bg-black/40 rounded-lg p-4">
                    <div class="text-3xl font-mono font-bold" x-text="timeLeft.minutes"></div>
                    <div class="text-xs opacity-70">Minutes</div>
                </div>
                <div class="bg-black/40 rounded-lg p-4">
                    <div class="text-3xl font-mono font-bold" x-text="timeLeft.seconds"></div>
                    <div class="text-xs opacity-70">Seconds</div>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm mb-4">
                <div class="bg-black/30 rounded-lg p-3">
                    <div class="opacity-80">Total Coins (Phase 1)</div>
                    <div class="font-mono font-bold" x-text="totalTokensForPhase.toLocaleString()"></div>
                </div>
                <div class="bg-black/30 rounded-lg p-3">
                    <div class="opacity-80">Price per RWAMP</div>
                    <div class="font-mono font-bold">$<span x-text="priceUsd.toFixed(3)"></span> USDT</div>
                </div>
                <div class="bg-black/30 rounded-lg p-3">
                    <div class="opacity-80">Private Sale Target</div>
                    <div class="font-mono font-bold">$<span x-text="privateSaleTargetUsd.toLocaleString()"></span> USDT</div>
                </div>
                <div class="bg-black/30 rounded-lg p-3">
                    <div class="opacity-80">Already Sold</div>
                    <div class="font-mono font-bold" x-text="soldTokens.toLocaleString() + ' tokens' "></div>
                </div>
            </div>

            <!-- Progress -->
            <div class="mb-2 flex items-center justify-between text-sm">
                <span class="opacity-80">Progress</span>
                <span class="font-mono" x-text="progress + '%'"></span>
            </div>
            <div class="w-full h-3 bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-accent" :style="`width: ${progress}%`"></div>
            </div>
            <div class="mt-2 text-xs opacity-70">
                Raised $<span class="font-mono" x-text="raisedUsd.toLocaleString(undefined, {minimumFractionDigits: 0})"></span> / $<span class="font-mono" x-text="(totalTokensForPhase * priceUsd).toLocaleString(undefined, {minimumFractionDigits: 0})"></span> USDT
            </div>

            <div class="mt-6">
                <button class="w-full bg-accent text-black py-3 rounded-lg font-montserrat font-bold hover:bg-yellow-400 transition">Learn More</button>
            </div>
        </div>
    </div>
</div>


