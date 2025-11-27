<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'role' => 'investor',
            'password' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->post('/dashboard/admin/users', $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'investor',
        ]);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $user = User::factory()->create(['role' => 'investor']);

        $response = $this->actingAs($this->admin)
            ->put("/dashboard/admin/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '9876543210',
                'role' => 'investor',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $user = User::factory()->create(['role' => 'investor']);

        $response = $this->actingAs($this->admin)
            ->delete("/dashboard/admin/users/{$user->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function admin_can_reset_user_password()
    {
        $user = User::factory()->create(['role' => 'investor']);

        $response = $this->actingAs($this->admin)
            ->post("/dashboard/admin/users/{$user->id}/reset-password", [
                'new_password' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function admin_can_assign_wallet_address()
    {
        $user = User::factory()->create([
            'role' => 'investor',
            'wallet_address' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/dashboard/admin/users/{$user->id}/assign-wallet");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $user->refresh();
        $this->assertNotNull($user->wallet_address);
        $this->assertEquals(16, strlen($user->wallet_address));
    }
}

