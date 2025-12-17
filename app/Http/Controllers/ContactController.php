<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ContactService;
use App\Services\EmailService;

class ContactController extends Controller
{
    protected ContactService $contactService;
    protected EmailService $emailService;

    public function __construct(ContactService $contactService, EmailService $emailService)
    {
        $this->contactService = $contactService;
        $this->emailService = $emailService;
    }

    /**
     * Store a new contact form submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|min:8|max:20',
            'message' => 'required|string|max:1000',
            'hp' => 'nullable|string|max:0',
            'recaptcha_token' => 'nullable|string',
        ]);

        try {
            // Optional reCAPTCHA v3 verification if configured
            if (config('services.recaptcha.secret_key') && ! empty($validated['recaptcha_token'])) {
                $resp = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => config('services.recaptcha.secret_key'),
                    'response' => $validated['recaptcha_token'],
                    'remoteip' => $request->ip(),
                ])->json();
                if (!($resp['success'] ?? false) || (($resp['score'] ?? 0) < (float) config('services.recaptcha.min_score'))) {
                    // Treat low scores as soft-fail to avoid UX friction
                    return back()->withErrors(['message' => 'Unable to verify your submission. Please try again.']);
                }
            }
            // Store the contact submission
            $contact = $this->contactService->createContact($validated);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.'
                ], 500);
            }
            return back()->withErrors(['message' => 'Something went wrong. Please try again later.']);
        }

        // Send emails but never block the user flow
        try {
            $this->emailService->sendContactNotification($contact);
        } catch (\Throwable $e) {
            // Silence email errors; they are logged inside EmailService
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.'
            ]);
        }
        return back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }
}
