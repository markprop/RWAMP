<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ResellerService;
use App\Services\EmailService;

class ResellerController extends Controller
{
    protected ResellerService $resellerService;
    protected EmailService $emailService;

    public function __construct(ResellerService $resellerService, EmailService $emailService)
    {
        $this->resellerService = $resellerService;
        $this->emailService = $emailService;
    }

    /**
     * Store a new reseller application.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'investmentCapacity' => 'required|string|in:1-10k,10-50k,50-100k,100k+',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            // Store the reseller application
            $reseller = $this->resellerService->createApplication($validated);

            // Send notification email
            $this->emailService->sendResellerNotification($reseller);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your application! We will contact you within 24 hours.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
