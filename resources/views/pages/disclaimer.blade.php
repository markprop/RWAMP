@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Disclaimer</h1>
            <p class="text-white/80 mt-2">Last updated: October 14, 2025</p>
        </div>
    </section>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">General Disclaimer</h2>
            <p class="text-gray-700">The information on this website is provided for general informational purposes only and should not be relied upon for any investment or legal decision without independent verification.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">Investment Risks</h2>
            <p class="text-gray-700">Investing in tokens and real estate-related products involves risks, including possible loss of principal. Past performance is not indicative of future results.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">No Guarantee of Performance</h2>
            <p class="text-gray-700">RWAMP does not guarantee any return, income, or performance of any asset or token.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">Third-Party Links</h2>
            <p class="text-gray-700">Our website may contain links to third-party websites. We do not control and are not responsible for the content, policies, or practices of those websites and encourage you to review their terms and privacy policies.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">No Legal Advice</h2>
            <p class="text-gray-700">Nothing on this site constitutes legal, tax, or financial advice. Always consult with qualified professionals.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">Limitation of Liability</h2>
            <p class="text-gray-700">RWAMP shall not be liable for any damages arising from the use of this website or reliance on any information provided herein.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">Updates</h2>
            <p class="text-gray-700">We may update this Disclaimer from time to time. Continued use of the site after updates signifies your acceptance of the changes.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <h2 class="text-2xl font-montserrat font-bold mb-3">Contact Us</h2>
            <p class="text-gray-700">For questions, contact <a href="mailto:info@rwamp.com" class="text-primary hover:underline">info@rwamp.com</a>.</p>
        </div>
    </div>
</div>
@endsection

