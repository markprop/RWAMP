<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResellerUserController extends Controller
{
    /**
     * Display all my users page
     */
    public function index(Request $request)
    {
        $reseller = Auth::user();
        
        $query = User::where('reseller_id', $reseller->id)
            ->withCount(['transactions', 'cryptoPayments']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('dashboard.reseller-users', compact('users'));
    }

    /**
     * View user details
     */
    public function show(User $user)
    {
        $reseller = Auth::user();
        
        // Verify user belongs to this reseller
        if ($user->reseller_id !== $reseller->id) {
            abort(403, 'Unauthorized');
        }

        $user->loadCount(['transactions', 'cryptoPayments']);
        $payments = $user->cryptoPayments()->latest()->paginate(10);
        $transactions = $user->transactions()->latest()->paginate(10);

        return view('dashboard.reseller-user-view', compact('user', 'payments', 'transactions'));
    }
}

