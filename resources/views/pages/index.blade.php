@extends('layouts.app')

@section('content')
<main class="min-h-screen">
    @include('components.hero-section')
    @include('components.about-section')
    @include('components.why-invest-section')
    @include('components.reseller-section')
    @include('components.roadmap-section')
    @include('components.signup-section')
    @include('components.footer')
</main>
@endsection
