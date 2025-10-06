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
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Store the contact submission
            $contact = $this->contactService->createContact($validated);

            // Send notification email
            $this->emailService->sendContactNotification($contact);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
