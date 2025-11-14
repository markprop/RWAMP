<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, TwoFactorAuthenticatable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
		'phone',
		'role',
		'company_name',
		'investment_capacity',
		'experience',
		'wallet_address',
		'token_balance',
		'coin_price',
		'referral_code',
		'reseller_id',
		'kyc_status',
		'kyc_id_type',
		'kyc_id_number',
		'kyc_full_name',
		'kyc_id_front_path',
		'kyc_id_back_path',
		'kyc_selfie_path',
		'kyc_submitted_at',
		'kyc_approved_at',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
		'kyc_submitted_at' => 'datetime',
		'kyc_approved_at' => 'datetime',
	];


	public function transactions()
	{
		return $this->hasMany(Transaction::class);
	}

	public function reseller()
	{
		return $this->belongsTo(User::class, 'reseller_id');
	}

	public function referredUsers()
	{
		return $this->hasMany(User::class, 'reseller_id');
	}

	public function cryptoPayments()
	{
		return $this->hasMany(CryptoPayment::class);
	}

	public function buyFromResellerRequests()
	{
		return $this->hasMany(BuyFromResellerRequest::class);
	}

	public function buyFromResellerRequestsAsReseller()
	{
		return $this->hasMany(BuyFromResellerRequest::class, 'reseller_id');
	}

	/**
	 * Add tokens to user's balance
	 */
	public function addTokens($amount, $description = 'Token credit')
	{
		$this->increment('token_balance', $amount);
		
		// Log transaction
		$this->transactions()->create([
			'type' => 'credit',
			'amount' => $amount,
			'description' => $description,
			'status' => 'completed'
		]);
	}

	/**
	 * Deduct tokens from user's balance
	 */
	public function deductTokens($amount, $description = 'Token deduction')
	{
		if ($this->token_balance >= $amount) {
			$this->decrement('token_balance', $amount);
			
			// Log transaction
			$this->transactions()->create([
				'type' => 'debit',
				'amount' => $amount,
				'description' => $description,
				'status' => 'completed'
			]);
			
			return true;
		}
		
		return false;
	}

	/**
	 * Check if user has sufficient token balance
	 */
	public function hasSufficientTokens($amount)
	{
		return $this->token_balance >= $amount;
	}

	/**
	 * Get formatted token balance
	 */
	public function getFormattedTokenBalance()
	{
		return number_format($this->token_balance, 0);
	}
}


