<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\CryptoPayment;
use App\Models\ResellerApplication;
use App\Models\WithdrawRequest;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCryptoPaymentController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminControllersTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $this->actingAs($this->admin);

        $controller = new AdminDashboardController();
        $response = $controller->index();

        $this->assertEquals('dashboard.admin', $response->name());
    }

    /** @test */
    public function admin_can_view_crypto_payments()
    {
        $user = User::factory()->create(['role' => 'investor']);
        CryptoPayment::factory()->create(['user_id' => $user->id]);

        $this->actingAs($this->admin);

        $controller = new AdminCryptoPaymentController();
        $response = $controller->index(request());

        $this->assertEquals('dashboard.admin-crypto', $response->name());
    }

    /** @test */
    public function admin_can_view_kyc_list()
    {
        $user = User::factory()->create([
            'role' => 'investor',
            'kyc_status' => 'pending',
            'kyc_submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/dashboard/admin/kyc');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin-kyc');
    }

    /** @test */
    public function admin_can_view_reseller_applications()
    {
        ResellerApplication::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get('/dashboard/admin/applications');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin-applications');
    }

    /** @test */
    public function admin_can_view_withdrawals()
    {
        $user = User::factory()->create(['role' => 'investor']);
        WithdrawRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->get('/dashboard/admin/withdrawals');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin-withdrawals');
    }

    /** @test */
    public function admin_can_view_price_management()
    {
        $response = $this->actingAs($this->admin)
            ->get('/dashboard/admin/prices');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin-prices');
    }

    /** @test */
    public function admin_can_view_sell_page()
    {
        $response = $this->actingAs($this->admin)
            ->get('/dashboard/admin/sell');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin-sell');
    }

    /** @test */
    public function admin_can_view_users()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/dashboard/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.admin-users');
    }

    /** @test */
    public function admin_can_approve_crypto_payment()
    {
        $user = User::factory()->create(['role' => 'investor']);
        $payment = CryptoPayment::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/dashboard/admin/crypto-payments/{$payment->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('crypto_payments', [
            'id' => $payment->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function admin_can_reject_crypto_payment()
    {
        $user = User::factory()->create(['role' => 'investor']);
        $payment = CryptoPayment::factory()->create([
            'user_id' => $user->id,
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
    public function admin_can_approve_kyc()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'kyc_status' => 'pending',
            'kyc_submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/dashboard/admin/kyc/{$user->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'kyc_status' => 'approved',
            'role' => 'investor',
        ]);
    }

    /** @test */
    public function admin_can_approve_reseller_application()
    {
        $application = ResellerApplication::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->put("/dashboard/admin/applications/{$application->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('reseller_applications', [
            'id' => $application->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'investor']);

        $response = $this->actingAs($user)
            ->get('/dashboard/admin');

        $response->assertRedirect(route('home'));
    }
}

