<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\ResellerApplication;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send contact notification email.
     */
    public function sendContactNotification(Contact $contact): bool
    {
        try {
            $data = [
                'contact' => $contact,
                'subject' => 'New Contact Form Submission - RWAMP',
            ];

            // Send to admin
            Mail::send('emails.contact-notification', $data, function ($message) use ($data) {
                $message->to(config('mail.admin_email', 'admin@rwamp.com'))
                        ->subject($data['subject']);
            });

            // Send confirmation to user
            Mail::send('emails.contact-confirmation', $data, function ($message) use ($contact) {
                $message->to($contact->email)
                        ->subject('Thank you for contacting RWAMP');
            });

            Log::info('Contact notification emails sent', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send contact notification emails', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send reseller notification email.
     */
    public function sendResellerNotification(ResellerApplication $application): bool
    {
        try {
            $data = [
                'application' => $application,
                'subject' => 'New Reseller Application - RWAMP',
            ];

            // Send to admin
            Mail::send('emails.reseller-notification', $data, function ($message) use ($data) {
                $message->to(config('mail.admin_email', 'admin@rwamp.com'))
                        ->subject($data['subject']);
            });

            // Send confirmation to user
            Mail::send('emails.reseller-confirmation', $data, function ($message) use ($application) {
                $message->to($application->email)
                        ->subject('Thank you for your Reseller Application - RWAMP');
            });

            Log::info('Reseller notification emails sent', [
                'application_id' => $application->id,
                'email' => $application->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send reseller notification emails', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send welcome email for newsletter subscription.
     */
    public function sendWelcomeEmail(NewsletterSubscription $subscription): bool
    {
        try {
            $data = [
                'subscription' => $subscription,
                'subject' => 'Welcome to RWAMP Newsletter',
            ];

            Mail::send('emails.newsletter-welcome', $data, function ($message) use ($subscription) {
                $message->to($subscription->email)
                        ->subject('Welcome to RWAMP Newsletter - Exclusive Updates Await!');
            });

            Log::info('Welcome email sent', [
                'subscription_id' => $subscription->id,
                'email' => $subscription->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send welcome email', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send newsletter to all active subscribers.
     */
    public function sendNewsletter(string $subject, string $content): bool
    {
        try {
            $subscribers = NewsletterSubscription::where('status', 'active')->get();
            
            foreach ($subscribers as $subscriber) {
                Mail::send('emails.newsletter', [
                    'content' => $content,
                    'subscriber' => $subscriber,
                ], function ($message) use ($subscriber, $subject) {
                    $message->to($subscriber->email)
                            ->subject($subject);
                });
            }

            Log::info('Newsletter sent to all subscribers', [
                'subscriber_count' => $subscribers->count(),
                'subject' => $subject,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send newsletter', [
                'error' => $e->getMessage(),
                'subject' => $subject,
            ]);
            return false;
        }
    }
}
