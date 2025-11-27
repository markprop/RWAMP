<?php

namespace App\Services;

use App\Models\ResellerApplication;
use Illuminate\Support\Facades\Log;

class ResellerService
{
    /**
     * Create a new reseller application.
     */
    public function createApplication(array $data): ResellerApplication
    {
        try {
            $application = ResellerApplication::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => null, // Password will be set when admin approves
                'company' => $data['company'] ?? null,
                'investment_capacity' => $data['investmentCapacity'],
                'message' => $data['message'] ?? null,
                'status' => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            Log::info('New reseller application created', [
                'application_id' => $application->id,
                'email' => $application->email,
                'name' => $application->name,
                'investment_capacity' => $application->investment_capacity,
            ]);

            return $application;
        } catch (\Exception $e) {
            Log::error('Failed to create reseller application', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Get all reseller applications with pagination.
     */
    public function getApplications(int $perPage = 15)
    {
        return ResellerApplication::orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Update application status.
     */
    public function updateStatus(ResellerApplication $application, string $status): bool
    {
        try {
            $application->update(['status' => $status]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update reseller application status', [
                'application_id' => $application->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get investment capacity options.
     */
    public function getInvestmentCapacityOptions(): array
    {
        return [
            '1-10k' => 'Rs 1,000 - Rs 10,000',
            '10-50k' => 'Rs 10,000 - Rs 50,000',
            '50-100k' => 'Rs 50,000 - Rs 100,000',
            '100k+' => 'Rs 100,000+',
        ];
    }
}
