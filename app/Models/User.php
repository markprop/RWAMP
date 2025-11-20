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
		'avatar',
		'status',
		'receipt_screenshot',
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
	 * Get chats where user is a participant
	 */
	public function chats()
	{
		return $this->belongsToMany(Chat::class, 'chat_participants')
			->withPivot(['is_admin', 'is_pinned', 'is_muted', 'is_archived', 'last_read_at', 'unread_count'])
			->withTimestamps();
	}

	/**
	 * Get messages sent by user
	 */
	public function sentMessages()
	{
		return $this->hasMany(ChatMessage::class, 'sender_id');
	}

	/**
	 * Check if user is admin
	 */
	public function isAdmin(): bool
	{
		return $this->role === 'admin';
	}

	/**
	 * Get avatar URL
	 */
	public function getAvatarUrlAttribute(): string
	{
		if ($this->avatar) {
			return asset('storage/' . $this->avatar);
		}
		return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=E30613&color=fff';
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

	/**
	 * Override twoFactorQrCodeSvg to handle decryption errors gracefully
	 */
	public function twoFactorQrCodeSvg()
	{
		try {
			// Call the trait method directly using the trait's implementation
			$svg = (new \BaconQrCode\Writer(
				new \BaconQrCode\Renderer\ImageRenderer(
					new \BaconQrCode\Renderer\RendererStyle\RendererStyle(192, 0, null, null, \BaconQrCode\Renderer\RendererStyle\Fill::uniformColor(new \BaconQrCode\Renderer\Color\Rgb(255, 255, 255), new \BaconQrCode\Renderer\Color\Rgb(45, 55, 72))),
					new \BaconQrCode\Renderer\Image\SvgImageBackEnd
				)
			))->writeString($this->twoFactorQrCodeUrl());

			return trim(substr($svg, strpos($svg, "\n") + 1));
		} catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
			\Log::error('Failed to decrypt two_factor_secret for QR code generation: ' . $e->getMessage());
			throw new \Exception('Unable to generate QR code. Your 2FA secret may be corrupted. Please disable and re-enable 2FA.');
		} catch (\Exception $e) {
			\Log::error('QR code generation error: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Override recoveryCodes to handle decryption errors gracefully
	 */
	public function recoveryCodes()
	{
		try {
			if (empty($this->two_factor_recovery_codes)) {
				return [];
			}
			// Call the trait's implementation directly
			return json_decode(\Laravel\Fortify\Fortify::currentEncrypter()->decrypt($this->two_factor_recovery_codes), true);
		} catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
			\Log::warning('Failed to decrypt recovery codes for user ' . $this->id . ': ' . $e->getMessage());
			return [];
		} catch (\Exception $e) {
			\Log::error('Recovery codes error for user ' . $this->id . ': ' . $e->getMessage());
			return [];
		}
	}

	/**
	 * Override twoFactorQrCodeUrl to handle decryption errors gracefully
	 */
	public function twoFactorQrCodeUrl()
	{
		try {
			return app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class)->qrCodeUrl(
				config('app.name'),
				$this->{config('fortify.username', 'email')},
				\Laravel\Fortify\Fortify::currentEncrypter()->decrypt($this->two_factor_secret)
			);
		} catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
			\Log::error('Failed to decrypt two_factor_secret for QR code URL: ' . $e->getMessage());
			throw new \Exception('Unable to generate QR code URL. Your 2FA secret may be corrupted. Please disable and re-enable 2FA.');
		}
	}
}


