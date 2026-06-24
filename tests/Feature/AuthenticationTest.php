<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_users_can_authenticate_and_access_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    public function test_inactive_users_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
