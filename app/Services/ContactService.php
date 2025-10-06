<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactService
{
    /**
     * Create a new contact submission.
     */
    public function createContact(array $data): Contact
    {
        try {
            $contact = Contact::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'],
                'status' => 'new',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('New contact submission created', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'name' => $contact->name,
            ]);

            return $contact;
        } catch (\Exception $e) {
            Log::error('Failed to create contact submission', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Get all contacts with pagination.
     */
    public function getContacts(int $perPage = 15)
    {
        return Contact::orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Update contact status.
     */
    public function updateStatus(Contact $contact, string $status): bool
    {
        try {
            $contact->update(['status' => $status]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update contact status', [
                'contact_id' => $contact->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
