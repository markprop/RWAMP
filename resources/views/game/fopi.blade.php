@extends('layouts.app')

@push('head')
    {{-- Load FOPI styles; keep close to the top of <head> --}}
    <link rel="stylesheet" href="{{ asset('css/fopi-game.css') }}">
@endpush

@section('content')
    {{-- FOPI game markup (password gate, intro, tutorial, dashboard, etc.) --}}
    {!! $fopiHtml !!}

<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js"></script>
<script>
// Initialize app with backend state & context
const INITIAL_STATE  = @json($initialState ?? []);
const GAME_SETTINGS  = @json($gameSettings ?? []);
const USER_CONTEXT   = @json($userContext ?? []);

// API abstraction layer
const api = {
    async getState() {
        const response = await fetch('/game/fopi/state', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async jumpMonth() {
        const response = await fetch('/game/fopi/jump-month', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async buyProperty(propertyId, sqft, feeMethod) {
        const response = await fetch('/game/fopi/buy', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ property_id: propertyId, sqft, fee_method: feeMethod }),
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async sellProperty(propertyId, sqft, feeMethod) {
        const response = await fetch('/game/fopi/sell', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ property_id: propertyId, sqft, fee_method: feeMethod }),
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async claimRent() {
        const response = await fetch('/game/fopi/claim-rent', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async convertFopiToRwamp(fopiAmount) {
        const response = await fetch('/game/fopi/convert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ fopi_amount: fopiAmount }),
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async claimMission(missionId) {
        const response = await fetch('/game/fopi/mission/claim', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ mission_id: missionId }),
        });
        const data = await response.json();
        return data.success ? data.state : null;
    },
    
    async saveState(state) {
        // State is saved automatically on backend after each action
        // This is a no-op for now, but can be used for explicit saves if needed
        return Promise.resolve();
    }
};

// Bootstrap hook for FOPI engine – expose a namespaced object while
// keeping backward compatibility with the original global `app`.
window.fopiGameBootstrap = {
    api,
    INITIAL_STATE,
    GAME_SETTINGS,
    USER_CONTEXT,
};
</script>
<script src="{{ asset('js/fopi-game.js') }}"></script>
@endsection
