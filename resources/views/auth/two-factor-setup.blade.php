@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-black via-secondary to-black">
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-8 card-hover animate-fadeInUp">
            <h1 class="text-2xl font-montserrat font-bold mb-4 text-center">Secure Your Account</h1>
            <p class="text-gray-600 text-sm mb-6 text-center">Enable two‑factor authentication to protect the admin dashboard.</p>

            @if (session('status') === 'two-factor-authentication-enabled')
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">Two‑factor authentication enabled.</div>
            @endif

            @if (! auth()->user()->two_factor_secret)
                <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="space-y-4 text-center">
                    @csrf
                    <button type="submit" class="w-full btn-primary">Enable Two‑Factor Authentication</button>
                </form>
            @else
                <p class="text-gray-700 mb-4 text-center">Scan this QR code with your authenticator app and store your recovery codes safely.</p>
                <div class="flex justify-center mb-6">
                    <div class="bg-white p-4 rounded border inline-block">{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>
                </div>

                <div class="mt-4">
                    <h4 class="font-montserrat font-bold mb-2">Recovery Codes</h4>
                    @php $codes = auth()->user()->recoveryCodes(); @endphp
                    <div class="grid md:grid-cols-2 gap-2">
                        @foreach($codes as $code)
                            <code class="bg-gray-100 rounded px-3 py-2 text-sm">{{ $code }}</code>
                        @endforeach
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <form method="POST" action="{{ url('user/two-factor-recovery-codes') }}">
                            @csrf
                            <button class="btn-secondary">Regenerate Codes</button>
                        </form>
                        <form method="POST" action="{{ url('user/two-factor-authentication') }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-primary">Disable 2FA</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

