<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerApplication;
use App\Models\User;
use App\Models\CryptoPayment;
use App\Models\WithdrawRequest;
use App\Helpers\PriceHelper;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display admin dashboard with metrics
     */
    public function index()
    {
        try {
            $users = User::query();
            $now = now();
            $metrics = [
                'users' => $users->count(),
                'resellers' => User::where('role','reseller')->count(),
                'investors' => User::where('role','investor')->count(),
                'new_users_7' => User::where('created_at','>=',$now->copy()->subDays(7))->count(),
                'new_users_30' => User::where('created_at','>=',$now->copy()->subDays(30))->count(),
                'pending_applications' => ResellerApplication::where('status','pending')->count(),
                'total_applications' => ResellerApplication::count(),
                'approved_applications' => ResellerApplication::where('status','approved')->count(),
                'rejected_applications' => ResellerApplication::where('status','rejected')->count(),
                'pending_kyc' => User::where('kyc_status','pending')->count(),
                'total_kyc' => User::whereIn('kyc_status', ['pending', 'approved', 'rejected'])->count(),
                'contacts' => \App\Models\Contact::count(),
                'coin_price' => PriceHelper::getRwampPkrPrice(),
                'crypto_payments' => CryptoPayment::count(),
                'pending_crypto_payments' => CryptoPayment::where('status','pending')->count(),
                'withdrawal_requests' => WithdrawRequest::count(),
                'pending_withdrawals' => WithdrawRequest::where('status','pending')->count(),
            ];

            return view('dashboard.admin', [
                'metrics' => $metrics,
                'applications' => ResellerApplication::latest()->limit(10)->get(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Admin dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return view('dashboard.admin', [
                'metrics' => [
                    'users' => 0,
                    'resellers' => 0,
                    'investors' => 0,
                    'new_users_7' => 0,
                    'new_users_30' => 0,
                    'pending_applications' => 0,
                    'total_applications' => 0,
                    'approved_applications' => 0,
                    'rejected_applications' => 0,
                    'pending_kyc' => 0,
                    'total_kyc' => 0,
                    'contacts' => 0,
                    'coin_price' => 0,
                    'crypto_payments' => 0,
                    'pending_crypto_payments' => 0,
                    'withdrawal_requests' => 0,
                    'pending_withdrawals' => 0,
                ],
                'applications' => collect([]),
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while loading the dashboard. Please check the logs.',
            ]);
        }
    }
}

