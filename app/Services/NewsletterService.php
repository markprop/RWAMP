<?php

namespace App\Services;

use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Log;

class NewsletterService
{
    /**
     * Create a new newsletter subscription.
     */
    public function createSubscription(array $data): NewsletterSubscription
    {
        try {
            // Check if email already exists
            $existingSubscription = NewsletterSubscription::where('email', $data['email'])->first();
            
            if ($existingSubscription) {
                // Update existing subscription
                $existingSubscription->update([
                    'whatsapp' => $data['whatsapp'] ?? $existingSubscription->whatsapp,
                    'status' => 'active',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                
                Log::info('Newsletter subscription updated', [
                    'subscription_id' => $existingSubscription->id,
                    'email' => $existingSubscription->email,
                ]);
                
                return $existingSubscription;
            }

            $subscription = NewsletterSubscription::create([
                'email' => $data['email'],
                'whatsapp' => $data['whatsapp'] ?? null,
                'status' => 'active',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('New newsletter subscription created', [
                'subscription_id' => $subscription->id,
                'email' => $subscription->email,
            ]);

            return $subscription;
        } catch (\Exception $e) {
            Log::error('Failed to create newsletter subscription', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Get all active subscriptions.
     */
    public function getActiveSubscriptions()
    {
        return NewsletterSubscription::where('status', 'active')->get();
    }

    /**
     * Unsubscribe an email.
     */
    public function unsubscribe(string $email): bool
    {
        try {
            $subscription = NewsletterSubscription::where('email', $email)->first();
            
            if ($subscription) {
                $subscription->update(['status' => 'unsubscribed']);
                Log::info('Newsletter subscription unsubscribed', [
                    'subscription_id' => $subscription->id,
                    'email' => $email,
                ]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to unsubscribe newsletter', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get subscription statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => NewsletterSubscription::count(),
            'active' => NewsletterSubscription::where('status', 'active')->count(),
            'unsubscribed' => NewsletterSubscription::where('status', 'unsubscribed')->count(),
            'this_month' => NewsletterSubscription::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
