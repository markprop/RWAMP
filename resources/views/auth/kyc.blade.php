@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    <section class="bg-gradient-to-r from-black to-secondary text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-3xl md:text-5xl font-montserrat font-bold">KYC Verification</h1>
            <p class="text-white/80">Complete your identity verification to access purchase and investor features.</p>
        </div>
    </section>

    <div class="max-w-4xl mx-auto px-4 py-10">
        @if (session('warning'))
            <div class="mb-6 rounded-lg border border-yellow-300 bg-yellow-50 text-yellow-800 px-4 py-3">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 text-red-800 px-4 py-3">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($user->kyc_status === 'pending')
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                <h3 class="font-montserrat font-bold text-lg text-blue-900 mb-2">KYC Under Review</h3>
                <p class="text-blue-800">Your KYC submission is currently under review. Admins will notify you once it's processed. Please check back soon.</p>
                <p class="text-sm text-blue-700 mt-2">Submitted on: {{ $user->kyc_submitted_at?->format('F d, Y H:i') ?? 'N/A' }}</p>
            </div>
        @elseif($user->kyc_status === 'rejected')
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-8">
                <h3 class="font-montserrat font-bold text-lg text-red-900 mb-2">KYC Rejected</h3>
                <p class="text-red-800">Your KYC submission was rejected. Please review the requirements and resubmit.</p>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 card-hover" x-data="kycForm('{{ old('kyc_id_type', '') }}')">
            <form method="POST" action="{{ route('kyc.submit') }}" enctype="multipart/form-data" @submit="submitForm($event)">
                @csrf

                <!-- Step Indicator -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold" 
                                 :class="step >= 1 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'">1</div>
                            <span class="font-montserrat font-semibold" :class="step >= 1 ? 'text-primary' : 'text-gray-600'">ID Type</span>
                        </div>
                        <div class="flex-1 h-1 mx-4" :class="step >= 2 ? 'bg-primary' : 'bg-gray-200'"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold" 
                                 :class="step >= 2 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'">2</div>
                            <span class="font-montserrat font-semibold" :class="step >= 2 ? 'text-primary' : 'text-gray-600'">ID Upload</span>
                        </div>
                        <div class="flex-1 h-1 mx-4" :class="step >= 3 ? 'bg-primary' : 'bg-gray-200'"></div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold" 
                                 :class="step >= 3 ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'">3</div>
                            <span class="font-montserrat font-semibold" :class="step >= 3 ? 'text-primary' : 'text-gray-600'">Selfie</span>
                        </div>
                    </div>
                </div>

                <!-- Step 1: ID Type Selection -->
                <div x-show="step === 1" x-cloak>
                    <h2 class="text-2xl font-montserrat font-bold mb-6">Step 1: Select ID Type</h2>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-blue-900 mb-2">📋 How to Choose:</h3>
                        <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
                            <li><strong>CNIC:</strong> If you are a Pakistani citizen living in Pakistan</li>
                            <li><strong>NICOP:</strong> If you are a Pakistani citizen living overseas</li>
                            <li><strong>Passport:</strong> If you are a foreign national or prefer using your passport</li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" 
                               :class="idType === 'cnic' ? 'border-primary bg-primary/5' : 'border-gray-200'">
                            <input type="radio" name="kyc_id_type" value="cnic" x-model="idType" class="mr-3" required>
                            <div class="flex-1">
                                <div class="font-semibold">CNIC (Computerized National Identity Card)</div>
                                <div class="text-sm text-gray-600">Pakistani National ID - Front and back required</div>
                                <div class="text-xs text-gray-500 mt-1">✓ For Pakistani citizens residing in Pakistan</div>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" 
                               :class="idType === 'nicop' ? 'border-primary bg-primary/5' : 'border-gray-200'">
                            <input type="radio" name="kyc_id_type" value="nicop" x-model="idType" class="mr-3" required>
                            <div class="flex-1">
                                <div class="font-semibold">NICOP (National Identity Card for Overseas Pakistanis)</div>
                                <div class="text-sm text-gray-600">Overseas Pakistani ID - Front and back required</div>
                                <div class="text-xs text-gray-500 mt-1">✓ For Pakistani citizens living abroad</div>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition" 
                               :class="idType === 'passport' ? 'border-primary bg-primary/5' : 'border-gray-200'">
                            <input type="radio" name="kyc_id_type" value="passport" x-model="idType" class="mr-3" required>
                            <div class="flex-1">
                                <div class="font-semibold">Passport</div>
                                <div class="text-sm text-gray-600">International passport - Front page only</div>
                                <div class="text-xs text-gray-500 mt-1">✓ For foreign nationals or passport holders</div>
                            </div>
                        </label>
                    </div>
                    <div class="mt-6">
                        <button type="button" @click="nextStep()" class="btn-primary">Next</button>
                    </div>
                </div>

                <!-- Step 2: ID Upload -->
                <div x-show="step === 2" x-cloak>
                    <h2 class="text-2xl font-montserrat font-bold mb-6">Step 2: Upload ID Documents</h2>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-green-900 mb-2">📸 Photo Guidelines:</h3>
                        <ul class="list-disc list-inside text-sm text-green-800 space-y-1">
                            <li>Take photos in good lighting (natural light is best)</li>
                            <li>Ensure the entire document is visible and in focus</li>
                            <li>All text and numbers must be clearly readable</li>
                            <li>No glare, shadows, or reflections on the document</li>
                            <li>File format: JPG or PNG only, maximum 5MB per file</li>
                        </ul>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name (as on ID) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kyc_full_name" value="{{ old('kyc_full_name') }}" 
                                   placeholder="Enter your full name exactly as it appears on your ID" 
                                   class="form-input" required>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Guide:</strong> Enter your complete name exactly as shown on your ID document. 
                                Include all names (first, middle, last) if present. Use the same spelling and format.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                ID Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kyc_id_number" value="{{ old('kyc_id_number') }}" 
                                   placeholder="Enter your ID number without spaces or dashes" 
                                   class="form-input" required>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Guide:</strong> 
                                <span x-show="idType === 'cnic' || idType === 'nicop'">Enter your 13-digit CNIC/NICOP number (e.g., 4410312345678). No spaces or dashes.</span>
                                <span x-show="idType === 'passport'">Enter your passport number exactly as shown (e.g., AB1234567). Include all letters and numbers.</span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                ID Front <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="kyc_id_front" accept="image/jpeg,image/jpg,image/png" class="form-input" required>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Guide:</strong> Upload a clear photo of the front side of your ID document. 
                                <span x-show="idType === 'cnic' || idType === 'nicop'">For CNIC/NICOP, this is the side with your photo and personal details.</span>
                                <span x-show="idType === 'passport'">For Passport, this is the page with your photo and personal information.</span>
                                Make sure all corners are visible and text is readable. JPG or PNG format, max 5MB.
                            </p>
                        </div>
                        <div x-show="idType === 'cnic' || idType === 'nicop'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                ID Back <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="kyc_id_back" accept="image/jpeg,image/jpg,image/png" class="form-input" 
                                   x-bind:required="idType === 'cnic' || idType === 'nicop'">
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Guide:</strong> Upload a clear photo of the back side of your CNIC/NICOP. 
                                This side typically contains additional information and security features. 
                                Ensure the entire back side is visible and all text is readable. JPG or PNG format, max 5MB.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="button" @click="prevStep()" class="btn-secondary">Back</button>
                        <button type="button" @click="goToStep3()" class="btn-primary">Next</button>
                    </div>
                </div>

                <!-- Step 3: Selfie -->
                <div x-show="step === 3" x-cloak>
                    <h2 class="text-2xl font-montserrat font-bold mb-6">Step 3: Selfie with ID</h2>
                    <div class="space-y-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="font-semibold text-blue-900 mb-2">📷 Step-by-Step Instructions:</h3>
                            <ol class="list-decimal list-inside text-sm text-blue-800 space-y-2">
                                <li><strong>Hold your ID:</strong> Hold your ID document in one hand, next to your face</li>
                                <li><strong>Position yourself:</strong> Stand or sit in front of a plain background (wall or door)</li>
                                <li><strong>Face the camera:</strong> Look directly at the camera with a neutral expression</li>
                                <li><strong>Show both clearly:</strong> Ensure both your face and the ID are fully visible in the frame</li>
                                <li><strong>Check readability:</strong> Make sure the ID details (name, number, photo) are clearly readable</li>
                                <li><strong>Good lighting:</strong> Use natural light or bright indoor lighting - avoid shadows on your face or ID</li>
                                <li><strong>No obstructions:</strong> Remove sunglasses, masks, or hats (religious headwear is acceptable)</li>
                                <li><strong>Take the photo:</strong> Have someone take the photo or use a selfie stick for better angle</li>
                            </ol>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 class="font-semibold text-yellow-900 mb-2">⚠️ Important Requirements:</h3>
                            <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                                <li>Your face must match the photo on the ID document</li>
                                <li>Both your face and ID must be in the same photo (no separate photos)</li>
                                <li>The ID must be the same document you uploaded in Step 2</li>
                                <li>Photo must be recent (taken within the last 7 days)</li>
                                <li>No filters, editing, or photo manipulation allowed</li>
                            </ul>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selfie with ID <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="kyc_selfie" accept="image/jpeg,image/jpg,image/png" class="form-input" required>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Guide:</strong> Upload a clear photo showing both your face and your ID document together. 
                                The photo should be taken in good lighting with both elements clearly visible. 
                                File format: JPG or PNG only, maximum 5MB.
                            </p>
                            <div class="mt-2 p-3 bg-gray-50 rounded border border-gray-200">
                                <p class="text-xs text-gray-600">
                                    <strong>💡 Tip:</strong> If using a phone, use the rear camera for better quality. 
                                    Make sure the camera is at eye level and you're looking directly at it.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-4">
                        <button type="button" @click="prevStep()" class="btn-secondary">Back</button>
                        <button type="submit" class="btn-primary">Submit KYC</button>
                    </div>
                </div>
            </form>

            <!-- Privacy Disclaimer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-600">
                    <strong>Privacy & Security:</strong> Your KYC data is encrypted and stored securely. 
                    Data will be deleted 30 days after approval or immediately upon rejection, per GDPR compliance. 
                    All files are stored on secure local servers and never shared with third parties.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

