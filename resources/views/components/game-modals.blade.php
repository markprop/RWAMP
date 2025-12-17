<!-- Game Warning Modal -->
<div x-show="showGameWarning" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 md:p-6"
     @click.self="showGameWarning = false"
     style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto max-h-[90vh] overflow-y-auto border border-gray-200">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-4 sm:px-6 pt-4 sm:pt-5 pb-3 rounded-t-xl z-10">
            <h3 class="text-xl sm:text-2xl font-bold text-red-600 flex items-center gap-2">
                <span class="text-2xl">⚠️</span>
                <span>REAL BALANCE LINKED</span>
            </h3>
        </div>
        
        <!-- Modal Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-5">
            <div class="space-y-4">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                    <ul class="space-y-2.5 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <span class="text-red-600 font-bold mt-0.5 flex-shrink-0">•</span>
                            <span>When you enter, your selected RWAMP coins are converted to FOPI game coins at 10× (1 RWAMP → 10 FOPI).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-600 font-bold mt-0.5 flex-shrink-0">•</span>
                            <span>A zero FOPI game balance means no RWAMP will be returned when you exit.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-600 font-bold mt-0.5 flex-shrink-0">•</span>
                            <span>On exit, your remaining FOPI are swapped back to RWAMP at 1 FOPI = 0.01 RWAMP (FOPI ÷ 100).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-600 font-bold mt-0.5 flex-shrink-0">•</span>
                            <span>Fees & spread are real — platform profits on every trade.</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <p class="font-semibold text-sm text-yellow-800 flex items-center gap-2">
                        <span>✅</span>
                        <span>I accept full financial responsibility.</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-4 sm:px-6 py-4 rounded-b-xl">
            <div class="flex gap-3 flex-col sm:flex-row">
                <button type="button"
                        @click.prevent="openGame()" 
                        class="flex-1 bg-gradient-to-r from-primary to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg text-sm sm:text-base">
                    I Understand - Continue
                </button>
                <button type="button"
                        @click.prevent="showGameWarning = false" 
                        class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-200 text-sm sm:text-base">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PIN Setup Modal -->
<div x-show="showPinSetup" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 md:p-6"
     @click.self="showPinSetup = false"
     style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto max-h-[90vh] overflow-y-auto border border-gray-200">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-4 sm:px-6 pt-4 sm:pt-5 pb-3 rounded-t-xl z-10">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
                <span x-show="gameType === 'trading'">Set Trading Game PIN</span>
                <span x-show="gameType === 'fopi'">Set FOPI Game PIN</span>
            </h3>
            <p class="text-sm text-gray-600 mt-1">Create a 4-digit PIN to secure your game sessions. Each game requires its own unique PIN.</p>
        </div>
        
        <!-- Modal Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-5">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">PIN (4 digits)</label>
                    <input type="password" 
                           x-model="pin" 
                           maxlength="4" 
                           pattern="\d{4}" 
                           class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                           placeholder="0000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm PIN</label>
                    <input type="password" 
                           x-model="pinConfirm" 
                           maxlength="4" 
                           pattern="\d{4}" 
                           class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                           placeholder="0000">
                </div>
                <div x-show="pinError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-2.5" x-text="pinError"></div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-4 sm:px-6 py-4 rounded-b-xl">
            <div class="flex gap-3 flex-col sm:flex-row">
                <button type="button"
                        @click.prevent="setupPin()" 
                        :disabled="pinLoading"
                        class="flex-1 bg-gradient-to-r from-primary to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                    <span x-show="!pinLoading">Set PIN</span>
                    <span x-show="pinLoading">Setting...</span>
                </button>
                <button type="button"
                        @click.prevent="closePinSetup()" 
                        class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-200 text-sm sm:text-base">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PIN Entry Modal -->
<div x-show="showPinEntry" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 md:p-6"
     @click.self="showPinEntry = false"
     style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto max-h-[90vh] overflow-y-auto border border-gray-200">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-4 sm:px-6 pt-4 sm:pt-5 pb-3 rounded-t-xl z-10">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
                <span x-show="gameType === 'trading'">Enter Trading Game PIN &amp; Stake</span>
                <span x-show="gameType === 'fopi'">Enter FOPI Game PIN &amp; Stake</span>
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Enter your 4-digit PIN and the number of RWAMP coins you want to transfer into the game.
                Those RWAMP coins will be locked from your real balance during the session.
            </p>
        </div>
        
        <!-- Modal Body -->
        <div class="px-4 sm:px-6 py-4 sm:py-5">
            @php
                $gameSettings = \App\Models\GameSetting::current();
            @endphp
            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-lg mb-4" x-show="gameType === 'trading'">
                <p class="text-xs text-gray-700 leading-relaxed">
                    <strong class="text-blue-900">Game coin is FOPI:</strong> When you enter, your staked RWAMP ×
                    <strong>{{ number_format($gameSettings->entry_multiplier, 2) }}</strong> becomes FOPI (in‑game coins).
                    When you exit, your remaining FOPI ÷ <strong>{{ number_format($gameSettings->exit_divisor, 2) }}</strong>
                    @if($gameSettings->exit_fee_rate > 0)
                        minus an exit fee of <strong>{{ number_format($gameSettings->exit_fee_rate, 2) }}%</strong>
                    @endif
                    is swapped back to RWAMP and added to your real balance.
                </p>
            </div>
            <div class="bg-purple-50 border-l-4 border-purple-500 p-3 rounded-lg mb-4" x-show="gameType === 'fopi'">
                @php
                    $fopiPerRwamp = $gameSettings->fopi_per_rwamp ?? 1000;
                @endphp
                <p class="text-xs text-gray-700 leading-relaxed">
                    <strong class="text-purple-900">FOPI Game:</strong> When you enter, your staked RWAMP is converted to FOPI game coins at 
                    <strong>1 RWAMP → {{ number_format($fopiPerRwamp, 0) }} FOPI</strong> for property investments. 
                    You can convert FOPI profits back to RWAMP within the game.
                </p>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">PIN</label>
                    <input type="password" 
                           x-model="pin" 
                           maxlength="4" 
                           pattern="\d{4}" 
                           class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                           placeholder="0000"
                           @keyup.enter="enterGame()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Stake Amount
                        <span class="text-xs font-normal text-gray-500">(RWAMP to convert into FOPI)</span>
                    </label>
                    <input type="number"
                           x-model="stakeAmount"
                           min="0.01"
                           step="0.01"
                           class="w-full px-4 py-2.5 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors"
                           placeholder="Enter tokens to move into game">
                </div>
                <div x-show="pinError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-2.5" x-text="pinError"></div>
                <div x-show="stakeError" class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-2.5" x-text="stakeError"></div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-4 sm:px-6 py-4 rounded-b-xl">
            <div class="flex gap-3 flex-col sm:flex-row">
                <button type="button"
                        @click.prevent="enterGame()" 
                        :disabled="pinLoading"
                        class="flex-1 bg-gradient-to-r from-primary to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base">
                    <span x-show="!pinLoading">Enter Game</span>
                    <span x-show="pinLoading">Entering...</span>
                </button>
                <button type="button"
                        @click.prevent="closePinEntry()" 
                        class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold py-2.5 sm:py-3 px-4 rounded-lg transition-all duration-200 text-sm sm:text-base">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Dialog Component -->
<div x-show="showAlert" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-3 sm:p-4 md:p-6"
     @click.self="closeAlert()"
     style="display: none;"
     x-cloak>
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto border border-gray-200">
        <div class="p-4 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <template x-if="alertType === 'success'">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="alertType === 'error'">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="alertType === 'warning'">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </template>
                    <template x-if="alertType === 'info'">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 mb-1" x-text="alertTitle || 'Information'"></h3>
                    <p class="text-sm text-gray-600 leading-relaxed" x-text="alertMessage || ''"></p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 border-t border-gray-200 px-4 sm:px-6 py-4 rounded-b-xl">
            <div class="flex justify-end">
                <button type="button"
                        @click.prevent="closeAlert()" 
                        class="bg-gradient-to-r from-primary to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg text-sm sm:text-base">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
