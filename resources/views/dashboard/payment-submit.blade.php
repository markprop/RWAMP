@extends('layouts.app')

@section('content')
@php
    $resellerOptions = $resellers->map(function ($r) {
        return ['id' => $r->id, 'name' => $r->name, 'email' => $r->email];
    });
@endphp
<div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    @include('components.investor-sidebar')
    
    <!-- Main Content Area (shifted right for sidebar) -->
    <div class="md:ml-64 min-h-screen">
        <!-- Top Header Bar -->
        <div class="bg-white shadow-sm border-b border-gray-200 sticky z-30" style="top: 28px;">
            <div class="px-4 sm:px-6 lg:px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-montserrat font-bold text-gray-900">Submit Bank Transfer Receipt</h1>
                        <p class="text-gray-500 text-sm mt-1.5">
                            Upload proof of your offline payment so an admin or reseller can validate and credit your RWAMP tokens.
                        </p>
                    </div>
                    <a href="{{ route('user.history') }}" class="btn-secondary">
                        860 Back to History
                    </a>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-xl shadow-xl p-6 max-w-3xl mx-auto"
                 x-data="resellerSelect()"
                 data-resellers='@json($resellerOptions)'>
                @if(session('status'))
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                        <strong class="font-semibold block mb-1">Please fix the following:</strong>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form 
                    method="POST" 
                    action="{{ route('payments.submit.store') }}" 
                    enctype="multipart/form-data"
                    class="space-y-6"
                >
                    @csrf

                    <!-- Recipient -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Send receipt to</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center gap-2">
                                <input 
                                    type="radio" 
                                    name="recipient_type" 
                                    value="admin" 
                                    class="text-primary focus:ring-primary"
                                    x-model="recipientType"
                                >
                                <span class="text-sm text-gray-800">RWAMP Admin (default)</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input 
                                    type="radio" 
                                    name="recipient_type" 
                                    value="reseller" 
                                    class="text-primary focus:ring-primary"
                                    x-model="recipientType"
                                >
                                <span class="text-sm text-gray-800">Specific Reseller</span>
                            </label>
                        </div>

                        <!-- Searchable reseller dropdown -->
                        <div class="mt-3" x-show="recipientType === 'reseller'">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Select reseller</label>
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @click.away="open = false"
                                    placeholder="Search reseller by name, email, or ID..."
                                    class="form-input w-full"
                                />
                                <div
                                    x-show="open"
                                    x-cloak
                                    class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto"
                                >
                                    <template x-for="item in filtered" :key="item.id">
                                        <button
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 flex items-center justify-between"
                                            @click="select(item)"
                                        >
                                            <span x-text="item.name"></span>
                                            <span class="text-xs text-gray-400" x-text="(item.email || '') + '  ID: ' + item.id"></span>
                                        </button>
                                    </template>
                                    <div x-show="!resellers.length" class="px-3 py-2 text-xs text-gray-500">
                                        No resellers found.
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="recipient_id" :value="selectedId">
                            <p class="mt-1 text-xs text-gray-500">
                                This reseller will see and approve/reject this payment on their Payments page.
                            </p>
                        </div>
                    </div>

                    <!-- Amounts -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Token Amount (RWAMP)</label>
                            <input 
                                type="number" 
                                step="0.00000001"
                                min="1"
                                name="token_amount" 
                                value="{{ old('token_amount') }}"
                                class="form-input w-full"
                                placeholder="e.g. 1000"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fiat Amount</label>
                            <div class="flex gap-2">
                                <input 
                                    type="number" 
                                    step="0.01"
                                    min="1"
                                    name="fiat_amount" 
                                    value="{{ old('fiat_amount') }}"
                                    class="form-input w-full"
                                    placeholder="e.g. 50000"
                                    required
                                />
                                <select 
                                    name="currency" 
                                    class="form-input w-28"
                                >
                                    <option value="PKR" {{ old('currency', 'PKR') === 'PKR' ? 'selected' : '' }}>PKR</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Bank details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                            <input 
                                type="text" 
                                name="bank_name" 
                                value="{{ old('bank_name') }}"
                                class="form-input w-full"
                                placeholder="e.g. HBL, Meezan"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Account (last 4 digits)</label>
                            <input 
                                type="text" 
                                name="account_last4" 
                                value="{{ old('account_last4') }}"
                                class="form-input w-full"
                                placeholder="1234"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Reference / UTR</label>
                            <input 
                                type="text" 
                                name="bank_reference" 
                                value="{{ old('bank_reference') }}"
                                class="form-input w-full"
                                placeholder="Optional reference / UTR"
                            />
                        </div>
                    </div>

                    <!-- Receipt upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload Receipt</label>
                        <input 
                            type="file" 
                            name="receipt" 
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-red-700"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Accepted formats: JPG, PNG, PDF. Max size 4 MB.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary w-full md:w-auto">
                            Submit Receipt for Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resellerSelect() {
    return {
        resellers: [],
        recipientType: '{{ old('recipient_type', 'admin') }}',
        search: '',
        open: false,
        selectedId: '{{ old('recipient_id') }}',
        init() {
            try {
                const raw = this.$el.dataset.resellers || '[]';
                this.resellers = JSON.parse(raw);
            } catch (e) {
                this.resellers = [];
            }
        },
        get filtered() {
            const base = this.resellers || [];
            const term = this.search.trim().toLowerCase();
            if (!term) return base;
            const filtered = base.filter((r) => {
                const name  = (r.name  || '').toLowerCase();
                const email = (r.email || '').toLowerCase();
                const idStr = String(r.id || '');
                return name.includes(term) || email.includes(term) || idStr.includes(term);
            });
            // If nothing matches, show full list instead of empty
            return filtered.length ? filtered : base;
        },
        select(item) {
            this.selectedId = item.id;
            this.search = item.name + ' (ID: ' + item.id + ')';
            this.open = false;
        }
    };
}
</script>
@endpush
