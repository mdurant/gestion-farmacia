<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserAccessLog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_creates_access_log_entry(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('user_access_logs', [
            'user_id' => $user->id,
            'disconnect_reason' => null,
        ]);

        $log = UserAccessLog::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($log?->browser);
        $this->assertNotNull($log?->connected_at);
    }

    public function test_profile_shows_access_audit_table(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        UserAccessLog::query()->create([
            'user_id' => $user->id,
            'session_token' => 'demo-token',
            'browser' => 'Google Chrome',
            'ip_address' => '127.0.0.1',
            'location' => 'Ubicación no disponible',
            'connected_at' => now()->subHour(),
            'disconnected_at' => now(),
            'disconnect_reason' => 'logout',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Auditoría de acceso')
            ->assertSee('Google Chrome');
    }

    public function test_admin_can_export_user_access_log_csv(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(UserRole::Admin->value);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::NursingTechnician->value);

        UserAccessLog::query()->create([
            'user_id' => $user->id,
            'session_token' => 'demo-token',
            'browser' => 'Safari',
            'ip_address' => '127.0.0.1',
            'location' => 'Santiago, Chile',
            'connected_at' => now()->subHours(2),
            'disconnected_at' => now()->subHour(),
            'disconnect_reason' => 'logout',
        ]);

        $this->actingAs($admin)
            ->get(route('users.access-log.export', ['user' => $user, 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
