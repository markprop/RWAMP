@extends('layouts.app')

@php
    $session = $session ?? auth()->user()->activeGameSession;
    $initialBalance = $initialBalance ?? ($session ? $session->calculateCurrentBalance() : 0);
    $initialPrices = $initialPrices ?? ['buy_price' => 0, 'sell_price' => 0, 'mid_price' => 0, 'btc_usd' => 0, 'usd_pkr' => 0];
    $gameSettings = \App\Models\GameSetting::current();
@endphp

@section('content')
<div class="min-h-screen game-gradient-bg text-slate-100" 
     x-data='gameSession({{ $initialBalance }}, @json($initialPrices), {{ $gameBalanceStart ?? $session->game_balance_start }}, {{ $realBalanceStart ?? $session->real_balance_start }}, @json($gameSettings))' 
     x-cloak>
    <!-- Game Header -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white py-4 sm:py-6 shadow-lg shadow-black/40">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">🎮 Trading Game</h1>
                    <p class="text-white/80 mt-1">BTC-Linked RWAMP Trading Simulation</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-white/80">Game Balance (FOPI)</div>
                    <div class="text-3xl font-bold" x-text="formatNumber(gameBalance)"></div>
                    <div class="text-sm text-white/80">FOPI (in-game coins)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6 rw-page-shell">
        <!-- Controls & Live Prices - compact metrics row -->
        <div class="grid md:grid-cols-3 lg:grid-cols-6 gap-3 mb-4 text-xs rw-grid-auto">
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-sm shadow-black/30 px-3 py-2">
                <div class="text-[10px] text-slate-400 mb-0.5">BTC/USDT</div>
                <div class="text-sm font-semibold" x-text="btcUsd ? btcUsd.toFixed(2) : '–'"></div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-sm shadow-black/30 px-3 py-2">
                <div class="text-[10px] text-slate-400 mb-0.5">USD/PKR</div>
                <div class="text-sm font-semibold" x-text="usdPkr ? usdPkr.toFixed(2) : '–'"></div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-sm shadow-black/30 px-3 py-2">
                <div class="text-[10px] text-slate-400 mb-0.5">RWAMP Mid Price</div>
                <div class="text-sm font-semibold text-emerald-400" x-text="midPrice ? midPrice.toFixed(4) : '–'"></div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-sm shadow-black/30 px-3 py-2">
                <div class="text-[10px] text-slate-400 mb-0.5">Buy (Ask)</div>
                <div class="text-sm font-semibold text-emerald-400" x-text="buyPrice ? buyPrice.toFixed(4) : '–'"></div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-sm shadow-black/30 px-3 py-2">
                <div class="text-[10px] text-slate-400 mb-0.5">Sell (Bid)</div>
                <div class="text-sm font-semibold text-rose-400" x-text="sellPrice ? sellPrice.toFixed(4) : '–'"></div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-sm shadow-black/30 px-3 py-2">
                <div class="text-[10px] text-slate-400 mb-0.5">Last Update</div>
                <div class="text-sm font-semibold" x-text="formatTime(lastUpdate)"></div>
            </div>
        </div>

        <!-- Price Display - large cards -->
        <div class="grid md:grid-cols-3 gap-4 rw-card-grid">
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-md shadow-black/40 p-4">
                <div class="text-sm text-slate-300 mb-1">Buy Price</div>
                <div class="text-2xl font-bold text-green-600" x-text="formatPrice(buyPrice)"></div>
                <div class="text-xs text-slate-400">PKR per RWAMP</div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-md shadow-black/40 p-4">
                <div class="text-sm text-slate-300 mb-1">Sell Price</div>
                <div class="text-2xl font-bold text-red-600" x-text="formatPrice(sellPrice)"></div>
                <div class="text-xs text-slate-400">PKR per RWAMP</div>
            </div>
            <div class="bg-slate-900 rounded-xl border border-slate-700/80 shadow-md shadow-black/40 p-4">
                <div class="text-sm text-slate-300 mb-1">Mid Price</div>
                <div class="text-2xl font-bold text-blue-600" x-text="formatPrice(midPrice)"></div>
                <div class="text-xs text-slate-400">PKR per RWAMP</div>
            </div>
        </div>

        <!-- Mid Price Chart + Portfolio Panel -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.9fr)] gap-4 items-stretch">
            <!-- Chart -->
            <div class="bg-slate-900 rounded-2xl border border-slate-700/80 shadow-md shadow-black/40 p-4 h-64 rw-chart-wrap">
                <div class="flex items-center justify-between mb-2">
                    <div class="text-xs text-slate-300 font-semibold">RWAMP Mid Price (PKR)</div>
                    <div class="flex items-center gap-2 text-[11px] text-slate-400">
                        <button type="button"
                                class="px-2 py-0.5 rounded-full border text-[10px]"
                                :class="currentTimeframe === '1m' ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200' : 'border-slate-600 bg-slate-800 text-slate-300'"
                                @click="setTimeframe('1m')">
                            1m
                        </button>
                        <button type="button"
                                class="px-2 py-0.5 rounded-full border text-[10px]"
                                :class="currentTimeframe === '5m' ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200' : 'border-slate-600 bg-slate-800 text-slate-300'"
                                @click="setTimeframe('5m')">
                            5m
                        </button>
                        <button type="button"
                                class="px-2 py-0.5 rounded-full border text-[10px]"
                                :class="currentTimeframe === '15m' ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200' : 'border-slate-600 bg-slate-800 text-slate-300'"
                                @click="setTimeframe('15m')">
                            15m
                        </button>
                        <button type="button"
                                class="px-2 py-0.5 rounded-full border text-[10px]"
                                :class="currentTimeframe === '1h' ? 'border-emerald-400 bg-emerald-500/20 text-emerald-200' : 'border-slate-600 bg-slate-800 text-slate-300'"
                                @click="setTimeframe('1h')">
                            1h
                        </button>
                    </div>
                </div>
                <div class="h-52">
                    <canvas id="gamePriceChart"></canvas>
                </div>
            </div>

            <!-- Portfolio / revenue panel -->
            <div class="bg-slate-900 rounded-2xl border border-slate-700/80 shadow-md shadow-black/40 p-4 space-y-2 text-xs">
                <div>
                    <div class="text-sm font-semibold text-slate-100">Portfolio &amp; Revenue</div>
                    <div class="text-[11px] text-slate-400">
                        Per-session balances and approximate PKR value based on current mid price.
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">PKR Balance (Est.)</div>
                        <div class="text-xs font-semibold text-amber-300" x-html="formatPriceTag(portfolioValuePkr || 0, {size: 'small', variant: 'dark'})"></div>
                    </div>
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">FOPI Balance (In‑Game)</div>
                        <div class="text-xs font-semibold" x-text="formatNumber(portfolioRwamp) + ' FOPI'"></div>
                    </div>
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">P&amp;L ₨ (This Session)</div>
                        <div class="text-xs font-semibold" :class="pnlPkr >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                             x-html="formatPriceTag(Math.abs(pnlPkr) || 0, {size: 'small', variant: 'dark'})"></div>
                    </div>
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">P&amp;L % (This Session)</div>
                        <div class="text-xs font-semibold" :class="pnlPct >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                             x-text="(pnlPct || 0).toFixed(2) + '%'"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-1">
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">Trades Today</div>
                        <div class="text-xs font-semibold" x-text="tradesToday || 0"></div>
                    </div>
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">Volume Today (FOPI)</div>
                        <div class="text-xs font-semibold" x-text="(volumeTodayRw || 0).toFixed(4)"></div>
                    </div>
                    <div class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1.5">
                        <div class="text-[10px] text-slate-400 mb-0.5">Volume Today (PKR)</div>
                        <div class="text-xs font-semibold" x-text="formatPrice(volumeTodayPkr || 0)"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trading Interface -->
        <div class="grid md:grid-cols-2 gap-6 rw-card-grid">
            <!-- Buy Section (FOPI in-game coin, priced in RWAMP/PKR) -->
            <div class="bg-slate-900 rounded-2xl border border-emerald-500/40 shadow-md shadow-black/40 p-6">
                <h3 class="text-xl font-bold mb-1 text-emerald-400">Buy FOPI</h3>
                <p class="text-[11px] text-slate-400 mb-4">You pay PKR to acquire FOPI game coins (priced using RWAMP rates).</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-200 mb-2">Quantity</label>
                        <input type="number" x-model="buyAmount" step="0.00000001" min="0.00000001" 
                               class="w-full px-4 py-2 border border-slate-700 rounded-lg bg-slate-950 text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none text-sm">
                    </div>
                    <div class="bg-slate-950/70 rounded-lg p-4 border border-slate-700/70">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-300">Price per FOPI (RWAMP-based):</span>
                            <span x-text="formatPrice(buyPrice)"></span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-300">Total cost:</span>
                            <span x-text="formatPrice(buyAmount * buyPrice)"></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-slate-300">Fee (1%):</span>
                            <span x-text="formatPrice((buyAmount * buyPrice) * 0.01)"></span>
                        </div>
                        <div class="border-t mt-2 pt-2 flex justify-between font-bold">
                            <span class="text-slate-200">Total (PKR):</span>
                            <span x-text="formatPrice((buyAmount * buyPrice) * 1.01)"></span>
                        </div>
                    </div>
                    <button @click="executeTrade('BUY')" 
                            :disabled="!canBuy || loading"
                            class="w-full bg-green-600 text-white py-3 rounded-lg font-bold hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Buy FOPI</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
            </div>

            <!-- Sell Section (FOPI in-game coin, priced in RWAMP/PKR) -->
            <div class="bg-slate-900 rounded-2xl border border-amber-400/50 shadow-md shadow-black/40 p-6">
                <h3 class="text-xl font-bold mb-1 text-amber-300">Sell FOPI</h3>
                <p class="text-[11px] text-slate-400 mb-4">You receive PKR when selling FOPI game coins (priced using RWAMP rates).</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-200 mb-2">Quantity</label>
                        <input type="number" x-model="sellAmount" step="0.00000001" min="0.00000001" 
                               class="w-full px-4 py-2 border border-slate-700 rounded-lg bg-slate-950 text-slate-100 focus:ring-2 focus:ring-amber-300 focus:outline-none text-sm">
                    </div>
                    <div class="bg-slate-950/70 rounded-lg p-4 border border-slate-700/70">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-300">Price per FOPI (RWAMP-based):</span>
                            <span x-text="formatPrice(sellPrice)"></span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-300">Total value:</span>
                            <span x-text="formatPrice(sellAmount * sellPrice)"></span>
                        </div>
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-slate-300">Fee (1%):</span>
                            <span x-text="formatPrice((sellAmount * sellPrice) * 0.01)"></span>
                        </div>
                        <div class="border-t mt-2 pt-2 flex justify-between font-bold">
                            <span class="text-slate-200">You receive (PKR):</span>
                            <span x-text="formatPrice((sellAmount * sellPrice) * 0.99)"></span>
                        </div>
                    </div>
                    <button @click="executeTrade('SELL')" 
                            :disabled="!canSell || loading"
                            class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Sell FOPI</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Trade History (Dynamic, no page reload) -->
        <div class="mt-6">
            <div class="bg-slate-900 rounded-2xl border border-slate-700/80 shadow-md shadow-black/40 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-100">Trade History (Current Session)</h3>
                        <p class="text-[11px] text-slate-400">
                            Latest trades are pulled dynamically from the server after each execution.
                        </p>
                    </div>
                </div>
                <div class="border border-slate-800 rounded-xl overflow-hidden max-h-72 overflow-y-auto text-xs">
                    <table class="min-w-full">
                        <thead class="bg-slate-950 sticky top-0 z-10">
                            <tr class="text-[11px] text-slate-400">
                                <th class="px-3 py-2 text-left">Time</th>
                                <th class="px-3 py-2 text-left">Side</th>
                                <th class="px-3 py-2 text-right">Qty (FOPI)</th>
                                <th class="px-3 py-2 text-right">Price (PKR)</th>
                                <th class="px-3 py-2 text-right">Fee (PKR)</th>
                                <th class="px-3 py-2 text-right">Net (PKR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!trades || trades.length === 0">
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                                        No trades yet. Use the Buy/Sell panels above to place your first trade.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="trade in trades" :key="trade.id">
                                <tr class="border-t border-slate-800">
                                    <td class="px-3 py-2 text-slate-300" x-text="formatTradeTime(trade.created_at)"></td>
                                    <td class="px-3 py-2 font-semibold"
                                        :class="trade.side === 'BUY' ? 'text-emerald-400' : 'text-rose-400'"
                                        x-text="trade.side"></td>
                                    <td class="px-3 py-2 text-right text-slate-200"
                                        x-text="parseFloat(trade.quantity || 0).toFixed(6)"></td>
                                    <td class="px-3 py-2 text-right text-slate-200"
                                        x-text="parseFloat(trade.price_pkr || 0).toFixed(4)"></td>
                                    <td class="px-3 py-2 text-right text-slate-400"
                                        x-text="parseFloat(trade.fee_pkr || 0).toFixed(2)"></td>
                                    <td class="px-3 py-2 text-right"
                                        :class="tradeNetClass(trade)"
                                        x-text="formatTradeNet(trade)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Exit Game Button -->
        <div class="text-center mt-6">
            <button @click="showExitModal = true" 
                    class="bg-slate-800 hover:bg-slate-700 text-white px-8 py-2.5 rounded-full font-semibold text-sm shadow-md shadow-black/40">
                Exit Game
            </button>
        </div>
    </div>

    <!-- Exit Confirmation Modal -->
    <div x-show="showExitModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4"
         @click.self="showExitModal = false">
        <div class="bg-slate-950 border border-slate-700 rounded-2xl max-w-md w-full p-6 text-slate-100 shadow-xl shadow-black/60">
            <h3 class="text-xl font-bold mb-4">Exit Game</h3>
            <div class="space-y-4">
                <div>
                    <div class="text-sm text-slate-300">Final Game Balance (FOPI):</div>
                    <div class="text-2xl font-bold" x-text="formatNumber(gameBalance) + ' FOPI'"></div>
                </div>
                <div>
                    <div class="text-sm text-slate-300">You Will Receive (RWAMP after swap):</div>
                    <div class="text-2xl font-bold text-emerald-400" x-text="formatNumber(exitPreviewRwamp()) + ' RWAMP'"></div>
                </div>
                <div class="flex gap-4">
                    <button @click="exitGame()" 
                            :disabled="exiting"
                            class="flex-1 bg-rose-500 hover:bg-rose-600 text-white py-2 rounded-full font-bold disabled:opacity-50">
                        <span x-show="!exiting">Apply to Real Account</span>
                        <span x-show="exiting">Processing...</span>
                    </button>
                    <button @click="showExitModal = false" 
                            class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-100 py-2 rounded-full font-bold">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

