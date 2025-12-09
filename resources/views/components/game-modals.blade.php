<!-- Game Warning Modal -->
<div x-show="showGameWarning" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 rw-modal rw-modal--mobile"
     @click.self="showGameWarning = false"
     style="display: none;">
    <div class="bg-white rounded-lg max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto rw-modal__panel">
        <h3 class="text-2xl font-bold mb-4 text-red-600">⚠️ REAL BALANCE LINKED</h3>
        <div class="space-y-4 text-gray-700">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <ul class="space-y-2 text-sm">
                    <li class="flex items-start">
                        <span class="font-bold mr-2">•</span>
                        <span>When you enter, your selected RWAMP coins are converted to FOPI game coins at 10× (1 RWAMP → 10 FOPI).</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold mr-2">•</span>
                        <span>A zero FOPI game balance means no RWAMP will be returned when you exit.</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold mr-2">•</span>
                        <span>On exit, your remaining FOPI are swapped back to RWAMP at 1 FOPI = 0.01 RWAMP (FOPI ÷ 100).</span>
                    </li>
                    <li class="flex items-start">
                        <span class="font-bold mr-2">•</span>
                        <span>Fees & spread are real — platform profits on every trade.</span>
                    </li>
                </ul>
            </div>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <p class="font-bold text-yellow-800">✅ I accept full financial responsibility.</p>
            </div>
        </div>
        <div class="flex gap-4 mt-6 flex-col sm:flex-row">
            <button @click="openGame()" 
                    class="flex-1 btn-primary btn-small rounded-lg font-bold">
                I Understand - Continue
            </button>
            <button @click="showGameWarning = false" 
                    class="flex-1 btn-secondary btn-small rounded-lg font-bold">
                Cancel
            </button>
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
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 rw-modal rw-modal--mobile"
     @click.self="showPinSetup = false"
     style="display: none;">
    <div class="bg-white rounded-lg max-w-md w-full p-6 rw-modal__panel">
        <h3 class="text-xl font-bold mb-4">Set Game PIN</h3>
        <p class="text-sm text-gray-600 mb-4">Create a 4-digit PIN to secure your game sessions.</p>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PIN (4 digits)</label>
                <input type="password" x-model="pin" maxlength="4" pattern="\d{4}" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"
                       placeholder="0000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm PIN</label>
                <input type="password" x-model="pinConfirm" maxlength="4" pattern="\d{4}" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"
                       placeholder="0000">
            </div>
            <div x-show="pinError" class="text-sm text-red-600" x-text="pinError"></div>
            <div class="flex gap-4 flex-col sm:flex-row">
                <button @click="setupPin()" 
                        :disabled="pinLoading"
                        class="flex-1 btn-primary btn-small rounded-lg font-bold disabled:opacity-50">
                    <span x-show="!pinLoading">Set PIN</span>
                    <span x-show="pinLoading">Setting...</span>
                </button>
                <button @click="showPinSetup = false" 
                        class="flex-1 btn-secondary btn-small rounded-lg font-bold">
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
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 rw-modal rw-modal--mobile"
     @click.self="showPinEntry = false"
     style="display: none;">
    <div class="bg-white rounded-lg max-w-md w-full p-6 rw-modal__panel">
        <h3 class="text-xl font-bold mb-4">Enter Game PIN &amp; Stake</h3>
        <p class="text-sm text-gray-600 mb-2">
            Enter your 4-digit PIN and the number of RWAMP coins you want to transfer into the game.
            Those RWAMP coins will be locked from your real balance during the session.
        </p>
        @php
            $gameSettings = \App\Models\GameSetting::current();
        @endphp
        <p class="text-xs text-gray-500 mb-4">
            Game coin is <strong>FOPI</strong>: when you enter, your staked RWAMP ×
            {{ number_format($gameSettings->entry_multiplier, 2) }} becomes FOPI (in‑game coins).
            When you exit, your remaining FOPI ÷ {{ number_format($gameSettings->exit_divisor, 2) }}
            @if($gameSettings->exit_fee_rate > 0)
                minus an exit fee of {{ number_format($gameSettings->exit_fee_rate, 2) }}%
            @endif
            is swapped back to RWAMP and added to your real balance.
        </p>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PIN</label>
                <input type="password" x-model="pin" maxlength="4" pattern="\d{4}" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"
                       placeholder="0000"
                       @keyup.enter="enterGame()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Stake Amount (RWAMP to convert into FOPI)</label>
                <input type="number"
                       x-model="stakeAmount"
                       min="0.01"
                       step="0.01"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary"
                       placeholder="Enter tokens to move into game">
            </div>
            <div x-show="pinError" class="text-sm text-red-600" x-text="pinError"></div>
            <div x-show="stakeError" class="text-sm text-red-600" x-text="stakeError"></div>
            <div class="flex gap-4 flex-col sm:flex-row">
                <button @click="enterGame()" 
                        :disabled="pinLoading"
                        class="flex-1 btn-primary btn-small rounded-lg font-bold disabled:opacity-50">
                    <span x-show="!pinLoading">Enter Game</span>
                    <span x-show="pinLoading">Entering...</span>
                </button>
                <button @click="showPinEntry = false; pin = ''; pinError = ''" 
                        class="flex-1 btn-secondary btn-small rounded-lg font-bold">
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
     class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4 rw-modal rw-modal--mobile"
     @click.self="showAlert = false; alertMessage = ''; alertType = 'info'"
     style="display: none;"
     x-cloak>
    <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl rw-modal__panel">
        <div class="flex items-start mb-4">
            <div class="flex-shrink-0">
                <template x-if="alertType === 'success'">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </template>
                <template x-if="alertType === 'error'">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </template>
                <template x-if="alertType === 'warning'">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </template>
                <template x-if="alertType === 'info'">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </template>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-lg font-bold text-gray-900" x-text="alertTitle || 'Information'"></h3>
                <p class="mt-1 text-sm text-gray-600" x-text="alertMessage || ''"></p>
            </div>
        </div>
        <div class="flex justify-end">
            <button @click="showAlert = false; alertMessage = ''; alertType = 'info'" 
                    class="btn-primary btn-small rounded-lg font-bold transition-colors">
                OK
            </button>
        </div>
    </div>
</div>
