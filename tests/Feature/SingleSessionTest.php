<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserAccessLog;
use App\Services\AccessLogService;
use App\Services\SingleSessionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SingleSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_prompts_to_close_other_devices_when_session_exists(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', $this->credentials($user))->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->current_session_id);

        $this->flushSession();
        auth()->logout();
        $this->assertGuest();

        $this->post('/login', $this->credentials($user))
            ->assertSessionHas('confirm_close_other_devices', true)
            ->assertSessionHas('active_session_info')
            ->assertSessionHas(AccessLogService::PENDING_LOGIN_KEY);
    }

    public function test_confirm_close_other_devices_logs_in_without_password(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', $this->credentials($user));
        $firstToken = $user->fresh()->current_session_id;

        $this->flushSession();
        auth()->logout();

        $this->post('/login', $this->credentials($user))
            ->assertSessionHas('confirm_close_other_devices', true);

        $this->post(route('login.confirm-other-devices'))
            ->assertRedirect(route('dashboard'));

        $this->assertNotEquals($firstToken, $user->fresh()->current_session_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_close_other_devices_claims_new_session(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', $this->credentials($user));
        $firstToken = $user->fresh()->current_session_id;

        $this->flushSession();
        auth()->logout();

        $this->post('/login', array_merge($this->credentials($user), [
            'close_other_devices' => '1',
        ]))->assertRedirect(route('dashboard'));

        $this->assertNotEquals($firstToken, $user->fresh()->current_session_id);
    }

    public function test_superseded_session_is_rejected_on_next_request(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'current_session_id' => 'token-dispositivo-activo',
        ]);
        $user->assignRole(UserRole::Admin->value);

        $this->withSession([
            SingleSessionService::SESSION_TOKEN_KEY => 'token-dispositivo-antiguo',
        ])->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_session_status_reports_superseded_session(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'current_session_id' => 'token-dispositivo-activo',
        ]);
        $user->assignRole(UserRole::Admin->value);

        DB::table('sessions')->insert([
            'id' => 'stale-session-row',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->timestamp,
        ]);

        $this->withSession([
            SingleSessionService::SESSION_TOKEN_KEY => 'token-dispositivo-antiguo',
        ])->withCookie(config('session.cookie'), 'stale-session-row')
            ->actingAs($user)
            ->getJson(route('session.status'))
            ->assertUnauthorized()
            ->assertJson([
                'expired' => true,
                'reason' => 'session_superseded',
            ]);
    }

    public function test_logout_releases_current_session_marker(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', $this->credentials($user));
        $this->assertNotNull($user->fresh()->current_session_id);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertNull($user->fresh()->current_session_id);
    }

    /** @return array<string, string> */
    private function credentials(User $user): array
    {
        return [
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ];
    }
}
