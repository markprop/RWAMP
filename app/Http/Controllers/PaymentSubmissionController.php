<?php

namespace App\Http\Controllers;

use App\Models\PaymentSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentSubmissionController extends Controller
{
    /**
     * Show the bank transfer / manual payment submission form.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        // Active resellers list for "send to" selector
        $resellers = User::where('role', 'reseller')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('dashboard.payment-submit', compact('user', 'resellers'));
    }

    /**
     * Store a new bank/manual payment submission.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'recipient_type' => 'required|in:admin,reseller',
            'recipient_id'   => 'nullable|exists:users,id',
            'token_amount'   => 'required|numeric|min:1',
            'fiat_amount'    => 'required|numeric|min:1',
            'currency'       => 'required|string|max:10',
            'bank_name'      => 'nullable|string|max:255',
            'account_last4'  => 'nullable|string|max:10',
            'bank_reference' => 'nullable|string|max:255',
            'receipt'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        if ($data['recipient_type'] === 'admin') {
            // For admin, keep recipient_id null (treated as global)
            $data['recipient_id'] = null;
        } elseif ($data['recipient_type'] === 'reseller') {
            // Require a reseller when specific reseller is chosen
            $request->validate([
                'recipient_id' => 'required|exists:users,id',
            ]);
        }

        $data['user_id'] = $user->id;

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')
                ->store('payment-receipts', 'public');
        }

        PaymentSubmission::create($data);

        return redirect()
            ->route('user.history')
            ->with('status', 'Payment receipt submitted. Waiting for review.');
    }

    /**
     * Stream the stored receipt file for a given PaymentSubmission.
     * This avoids relying on public storage paths differing between environments.
     */
    public function showReceipt(Request $request, PaymentSubmission $submission)
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Authorization: owner of submission, assigned reseller, or admin
        $isOwner    = $submission->user_id === $user->id;
        $isReseller = $submission->recipient_type === 'reseller' && $submission->recipient_id === $user->id;
        $isAdmin    = method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : ($user->role ?? null) === 'admin';

        if (!($isOwner || $isReseller || $isAdmin)) {
            abort(403);
        }

        if (empty($submission->receipt_path)) {
            abort(404);
        }

        $path = $submission->receipt_path;

        // Our uploads use the 'public' disk by default
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
