<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\NewsletterService;
use App\Services\EmailService;

class NewsletterController extends Controller
{
    protected NewsletterService $newsletterService;
    protected EmailService $emailService;

    public function __construct(NewsletterService $newsletterService, EmailService $emailService)
    {
        $this->newsletterService = $newsletterService;
        $this->emailService = $emailService;
    }

    /**
     * Store a new newsletter subscription.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'hp' => 'nullable|string|max:0',
        ]);

        try {
            // Store the newsletter subscription
            $subscription = $this->newsletterService->createSubscription($validated);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.'
                ], 500);
            }
            return back()->withErrors(['message' => 'Something went wrong. Please try again later.']);
        }

        // Send welcome email but never block the UX
        try {
            $this->emailService->sendWelcomeEmail($subscription);
        } catch (\Throwable $e) {
            // Silence email errors
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! You will receive exclusive updates about RWAMP.'
            ]);
        }
        return back()->with('success', 'Thank you for subscribing! You will receive exclusive updates about RWAMP.');
    }
}
