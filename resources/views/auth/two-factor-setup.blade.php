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

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            @endif

            @if(isset($error))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <p class="text-sm">{{ $error }}</p>
                </div>
            @endif

            @if(isset($hasCorruptedSecret) && $hasCorruptedSecret)
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <p class="text-sm font-semibold">⚠️ Warning: Your 2FA secret is corrupted.</p>
                    <p class="text-sm mt-1">This usually happens if the application key has changed. Please disable and re-enable 2FA to generate a new secret.</p>
                </div>
            @endif

            @if(isset($hasCorruptedRecoveryCodes) && $hasCorruptedRecoveryCodes)
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-4">
                    <p class="text-sm font-semibold">⚠️ Warning: Your recovery codes cannot be decrypted.</p>
                    <p class="text-sm mt-1">Please regenerate your recovery codes using the button below.</p>
                </div>
            @endif

            @php
                $secretExists = isset($secretExists) ? $secretExists : (!empty(auth()->user()->getAttribute('two_factor_secret')));
                $shouldShowEnable = !$secretExists || (isset($hasCorruptedSecret) && $hasCorruptedSecret);
            @endphp

            @if($shouldShowEnable)
                <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="space-y-4 text-center">
                    @csrf
                    <button type="submit" class="w-full btn-primary">Enable Two‑Factor Authentication</button>
                </form>
            @else
                @php
                    $qrCodeError = false;
                    $codes = [];
                    $recoveryCodesError = false;
                    
                    // Try to generate QR code
                    try {
                        $qrCode = auth()->user()->twoFactorQrCodeSvg();
                    } catch (\Exception $e) {
                        $qrCodeError = true;
                        $qrCode = null;
                        \Log::error('QR code generation error: ' . $e->getMessage());
                    }
                    
                    // Try to get recovery codes
                    try {
                        if (auth()->user()->two_factor_recovery_codes) {
                            $codes = auth()->user()->recoveryCodes();
                            if (!is_array($codes)) {
                                $codes = [];
                            }
                        }
                    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                        // Recovery codes cannot be decrypted (likely due to key change)
                        $recoveryCodesError = true;
                        $codes = [];
                        \Log::warning('Recovery codes decryption error for user ' . auth()->id() . ': ' . $e->getMessage());
                    } catch (\Exception $e) {
                        $recoveryCodesError = true;
                        $codes = [];
                        \Log::error('Recovery codes error: ' . $e->getMessage());
                    }
                @endphp

                @if($qrCodeError)
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <p class="text-sm font-semibold">Error generating QR code.</p>
                        <p class="text-sm mt-1">Your 2FA secret may be corrupted. Please disable and re-enable 2FA.</p>
                    </div>
                @else
                    <p class="text-gray-700 mb-4 text-center">Scan this QR code with your authenticator app and store your recovery codes safely.</p>
                    <div class="flex justify-center mb-6">
                        <div class="bg-white p-4 rounded border inline-block">{!! $qrCode !!}</div>
                    </div>
                @endif

                <div class="mt-4">
                    <h4 class="font-montserrat font-bold mb-2">Recovery Codes</h4>
                    @if($recoveryCodesError)
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                            <p class="text-sm font-semibold">Recovery codes cannot be decrypted.</p>
                            <p class="text-sm mt-1">This usually happens if the application key has changed. Please regenerate your recovery codes immediately.</p>
                        </div>
                    @elseif(empty($codes))
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-4">
                            <p class="text-sm">Recovery codes are not available. Please regenerate them.</p>
                        </div>
                    @else
                        <div class="grid md:grid-cols-2 gap-2">
                            @foreach($codes as $code)
                                <code class="bg-gray-100 rounded px-3 py-2 text-sm">{{ $code }}</code>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 flex items-center gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.2fa.regenerate-recovery-codes') }}" class="inline-block">
                            @csrf
                            <button type="submit" class="btn-secondary">Regenerate Codes</button>
                        </form>
                        <form method="POST" action="{{ url('user/two-factor-authentication') }}" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-primary">Disable 2FA</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

