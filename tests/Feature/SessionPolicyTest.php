<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_initializes_session_timestamps(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $response = $this->getJson(route('session.status'));
        $response->assertOk();
        $response->assertJsonStructure([
                'absolute_lifetime_seconds',
                'idle_threshold_seconds',
                'warning_countdown_seconds',
                'absolute_remaining_seconds',
                'idle_elapsed_seconds',
                'idle_remaining_seconds',
                'show_warning',
                'warning_remaining_seconds',
                'expired',
            ]);
    }

    public function test_session_renew_endpoint_resets_idle_timer(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ]);

        $this->travel(5)->minutes();

        $this->post(route('session.renew'))
            ->assertOk()
            ->assertJson(['ok' => true]);

        $status = $this->get(route('session.status'))->json();

        $this->assertFalse($status['show_warning']);
        $this->assertGreaterThan(400, $status['idle_remaining_seconds']);
    }

    public function test_absolute_session_lifetime_logs_user_out(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ]);

        $this->travel(61)->minutes();

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_idle_timeout_with_warning_grace_logs_user_out(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ]);

        $this->travel(16)->minutes();

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
