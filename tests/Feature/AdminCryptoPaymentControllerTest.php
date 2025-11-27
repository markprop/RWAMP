<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CryptoPayment;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminCryptoPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        $this->user = User::factory()->create([
            'role' => 'investor',
            'token_balance' => 0,
        ]);
    }

    /** @test */
    public function admin_can_approve_crypto_payment_and_credit_tokens()
    {
        $payment = CryptoPayment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'token_amount' => 1000,
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/dashboard/admin/crypto-payments/{$payment->id}/approve");

        $response->assertRedirect();
        
        $this->assertDatabaseHas('crypto_payments', [
            'id' => $payment->id,
            'status' => 'approved',
        ]);

        $this->user->refresh();
        $this->assertEquals(1000, $this->user->token_balance);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'crypto_purchase',
            'amount' => 1000,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function admin_can_reject_crypto_payment()
    {
        $payment = CryptoPayment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/dashboard/admin/crypto-payments/{$payment->id}/reject");

        $response->assertRedirect();
        $this->assertDatabaseHas('crypto_payments', [
            'id' => $payment->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function admin_can_update_crypto_payment()
    {
        $payment = CryptoPayment::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
            'token_amount' => 500,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/dashboard/admin/crypto-payments/{$payment->id}", [
                'token_amount' => 1000,
                'usd_amount' => '100',
                'pkr_amount' => '28000',
                'network' => 'TRC20',
                'tx_hash' => 'new_hash_123',
                'status' => 'approved',
                'notes' => 'Updated payment',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('crypto_payments', [
            'id' => $payment->id,
            'token_amount' => 1000,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_delete_crypto_payment()
    {
        $payment = CryptoPayment::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/dashboard/admin/crypto-payments/{$payment->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('crypto_payments', ['id' => $payment->id]);
    }

    /** @test */
    public function admin_can_view_payment_details()
    {
        $payment = CryptoPayment::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/dashboard/admin/crypto-payments/{$payment->id}/details");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'payment',
            'user',
        ]);
    }
}

