<?php

namespace App\Models;

use App\Concerns\HasUlid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens, Notifiable, TwoFactorAuthenticatable, HasFactory, HasUlid;

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
        'kyc_rejection_reason',
		'avatar',
		'status',
		'receipt_screenshot',
		'game_pin_hash',
		'trading_game_pin_hash',
		'fopi_game_pin_hash',
		'is_in_game',
		'game_pin_locked_until',
		'game_pin_failed_attempts',
		'trading_game_pin_failed_attempts',
		'fopi_game_pin_failed_attempts',
		'trading_game_pin_locked_until',
		'fopi_game_pin_locked_until',
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
		'is_in_game' => 'boolean',
		'game_pin_locked_until' => 'datetime',
		'trading_game_pin_locked_until' => 'datetime',
		'fopi_game_pin_locked_until' => 'datetime',
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
	 * Calculate token balance from transaction history
	 * This ensures balance consistency by summing all transaction amounts
	 */
	public function calculateBalanceFromTransactions(): float
	{
		$balance = 0.0;
		
		// Sum all completed transactions for this user
		$transactions = $this->transactions()
			->where('status', 'completed')
			->get();
		
		foreach ($transactions as $transaction) {
			// Add the transaction amount to balance
			// Positive amounts = credits, negative amounts = debits
			$balance += (float) $transaction->amount;
		}
		
		return max(0, $balance); // Balance cannot be negative
	}

	/**
	 * Reconcile balance with transaction history
	 * Returns array with calculated balance and discrepancy
	 */
	public function reconcileBalance(): array
	{
		$calculatedBalance = $this->calculateBalanceFromTransactions();
		$storedBalance = (float) ($this->token_balance ?? 0);
		$discrepancy = $calculatedBalance - $storedBalance;
		
		return [
			'calculated_balance' => $calculatedBalance,
			'stored_balance' => $storedBalance,
			'discrepancy' => $discrepancy,
			'is_consistent' => abs($discrepancy) < 0.01, // Allow small floating point differences
		];
	}

	/**
	 * Fix balance by recalculating from transactions
	 * Use with caution - logs the fix for audit purposes
	 */
	public function fixBalanceFromTransactions(): bool
	{
		$oldBalance = (float) ($this->token_balance ?? 0);
		$calculatedBalance = $this->calculateBalanceFromTransactions();
		
		if (abs($oldBalance - $calculatedBalance) >= 0.01) {
			\Log::warning('Balance reconciliation fix applied', [
				'user_id' => $this->id,
				'user_email' => $this->email,
				'old_balance' => $oldBalance,
				'calculated_balance' => $calculatedBalance,
				'discrepancy' => $calculatedBalance - $oldBalance,
			]);
			
			$this->token_balance = $calculatedBalance;
			return $this->save();
		}
		
		return true;
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

	/**
	 * Get the email address for password reset (required by CanResetPassword interface)
	 */
	public function getEmailForPasswordReset()
	{
		return $this->email;
	}

	/**
	 * Send the password reset notification (required by CanResetPassword interface)
	 */
	public function sendPasswordResetNotification($token)
	{
		// Use Laravel's default password reset notification
		// This can be customized if needed
		$this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
	}

	/**
	 * Get game sessions for this user
	 */
	public function gameSessions()
	{
		return $this->hasMany(GameSession::class);
	}

	/**
	 * Get active game session
	 */
	public function activeGameSession()
	{
		return $this->hasOne(GameSession::class)->where('status', 'active');
	}

	/**
	 * Set game PIN (4-digit, bcrypt hashed)
	 */
	public function setGamePin(string $pin): bool
	{
		if (!preg_match('/^\d{4}$/', $pin)) {
			return false;
		}

		$this->game_pin_hash = \Hash::make($pin);
		$this->game_pin_failed_attempts = 0;
		$this->game_pin_locked_until = null;
		return $this->save();
	}

	/**
	 * Verify game PIN
	 */
	public function verifyGamePin(string $pin): bool
	{
		// Check if locked
		if ($this->game_pin_locked_until && $this->game_pin_locked_until->isFuture()) {
			return false;
		}

		// Check if PIN is set
		if (!$this->game_pin_hash) {
			return false;
		}

		// Verify PIN
		if (\Hash::check($pin, $this->game_pin_hash)) {
			// Reset failed attempts on success
			$this->game_pin_failed_attempts = 0;
			$this->game_pin_locked_until = null;
			$this->save();
			return true;
		}

		// Increment failed attempts
		$this->increment('game_pin_failed_attempts');

		// Lock after 3 failed attempts
		if ($this->game_pin_failed_attempts >= 3) {
			$this->game_pin_locked_until = now()->addMinutes(5);
			$this->save();
		}

		return false;
	}

	/**
	 * Check if game PIN is locked
	 */
	public function isGamePinLocked(): bool
	{
		return $this->game_pin_locked_until && $this->game_pin_locked_until->isFuture();
	}

	/**
	 * Set game-specific PIN (for Trading or FOPI game)
	 */
	public function setGameSpecificPin(string $pin, string $gameType): bool
	{
		if (!preg_match('/^\d{4}$/', $pin)) {
			return false;
		}

		if ($gameType === 'trading') {
			$this->trading_game_pin_hash = \Hash::make($pin);
			$this->trading_game_pin_failed_attempts = 0;
			$this->trading_game_pin_locked_until = null;
		} elseif ($gameType === 'fopi') {
			$this->fopi_game_pin_hash = \Hash::make($pin);
			$this->fopi_game_pin_failed_attempts = 0;
			$this->fopi_game_pin_locked_until = null;
		} else {
			return false;
		}

		return $this->save();
	}

	/**
	 * Verify game-specific PIN
	 */
	public function verifyGameSpecificPin(string $pin, string $gameType): bool
	{
		$pinHashField = $gameType === 'trading' ? 'trading_game_pin_hash' : 'fopi_game_pin_hash';
		$lockedUntilField = $gameType === 'trading' ? 'trading_game_pin_locked_until' : 'fopi_game_pin_locked_until';
		$failedAttemptsField = $gameType === 'trading' ? 'trading_game_pin_failed_attempts' : 'fopi_game_pin_failed_attempts';

		// Check if locked
		$lockedUntil = $this->$lockedUntilField;
		if ($lockedUntil && \Carbon\Carbon::parse($lockedUntil)->isFuture()) {
			return false;
		}

		// Check if PIN is set
		$pinHash = $this->$pinHashField;
		if (!$pinHash) {
			return false;
		}

		// Verify PIN
		if (\Hash::check($pin, $pinHash)) {
			// Reset failed attempts on success
			$this->$failedAttemptsField = 0;
			$this->$lockedUntilField = null;
			$this->save();
			return true;
		}

		// Increment failed attempts
		$this->increment($failedAttemptsField);

		// Lock after 3 failed attempts
		if ($this->$failedAttemptsField >= 3) {
			$this->$lockedUntilField = now()->addMinutes(5);
			$this->save();
		}

		return false;
	}

	/**
	 * Check if game-specific PIN is set
	 */
	public function hasGamePin(string $gameType): bool
	{
		if ($gameType === 'trading') {
			return !empty($this->trading_game_pin_hash);
		} elseif ($gameType === 'fopi') {
			return !empty($this->fopi_game_pin_hash);
		}
		return false;
	}

	/**
	 * Check if game-specific PIN is locked
	 */
	public function isGameSpecificPinLocked(string $gameType): bool
	{
		$lockedUntilField = $gameType === 'trading' ? 'trading_game_pin_locked_until' : 'fopi_game_pin_locked_until';
		$lockedUntil = $this->$lockedUntilField;
		return $lockedUntil && \Carbon\Carbon::parse($lockedUntil)->isFuture();
	}

	/**
	 * Check if user can enter game.
	 * Keep this simple: only KYC + PIN lock. Balance and session checks are
	 * enforced when creating a new game session (GameController::enter).
	 * Note: If user is already in a game, they can still access the game page to resume.
	 */
	public function canEnterGame(): bool
	{
		return $this->kyc_status === 'approved'
			&& !$this->isGamePinLocked();
	}
}


