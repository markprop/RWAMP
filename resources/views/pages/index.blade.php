@extends('layouts.app')

@section('content')
<div class="min-h-screen" x-data x-init="
    @if(request()->query('open') === 'purchase')
        $nextTick(() => window.dispatchEvent(new CustomEvent('open-purchase-modal')))
    @endif
">
    @include('components.hero-section', ['presaleData' => $presaleData ?? []])
    @include('components.about-section')
    @include('components.why-invest-section')
    @include('components.roadmap-section')
    @include('components.signup-section')
    @include('components.footer')
    
    <!-- Purchase modal available on homepage too -->
    @include('components.purchase-modal', ['rates' => $rates ?? [], 'wallets' => $wallets ?? []])
</div>
@endsection
