@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <!-- Hero -->
    <section class="bg-gradient-to-r from-black to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">Privacy Policy</h1>
            <p class="text-white/80 mt-2">Last updated: October 14, 2025</p>
        </div>
    </section>

    <!-- Content -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Introduction -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Introduction</h2>
            <p class="text-gray-700">This Privacy Policy explains how RWAMP ("we", "us", or "our") collects, uses, and protects your information when you use our website and related services.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Information We Collect -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Information We Collect</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-1">
                <li>Contact details (name, email, phone).</li>
                <li>Account information and preferences.</li>
                <li>Technical data such as IP address and device info.</li>
                <li>Usage analytics and cookies for performance and security.</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- How We Use Your Information -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">How We Use Your Information</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-1">
                <li>To provide, personalize, and improve our services.</li>
                <li>To communicate updates, security notices, and support.</li>
                <li>To process transactions and verify identity where applicable.</li>
                <li>To comply with legal obligations and prevent fraud and abuse.</li>
            </ul>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Information Sharing -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Information Sharing</h2>
            <p class="text-gray-700">We do not sell your personal information. We may share data with trusted service providers under strict agreements, or when required by law.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Data Security -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Data Security</h2>
            <p class="text-gray-700">We implement administrative, technical, and physical safeguards to protect your information (e.g., encryption in transit, access controls). However, no method of transmission over the Internet is 100% secure.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Your Rights -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Your Rights</h2>
            <p class="text-gray-700">Depending on your location, you may have rights to access, correct, delete, or restrict the processing of your personal data, to withdraw consent, and to lodge a complaint with a supervisory authority.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Changes to Policy -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Changes to Policy</h2>
            <p class="text-gray-700">We may update this policy to reflect changes to our practices. Any updates will be posted on this page with the revised date.</p>
        </div>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover animate-fadeInUp">
            <!-- Contact Us -->
            <h2 class="text-2xl font-montserrat font-bold mb-3">Contact Us</h2>
            <p class="text-gray-700">If you have questions, contact us at <a href="mailto:info@rwamp.com" class="text-primary hover:underline">info@rwamp.com</a>.</p>
        </div>
    </div>
</div>
@endsection

