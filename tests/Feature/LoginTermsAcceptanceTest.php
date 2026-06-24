<?php

namespace Tests\Feature;

use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTermsAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_screen_shows_terms_checkbox_and_modal(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Recordarme en este equipo', false)
            ->assertSee('términos y condiciones de uso', false)
            ->assertSee('Tratamiento de datos personales', false)
            ->assertSee('Versión '.config('acalis.terms.version'), false);
    }

    public function test_login_requires_terms_acceptance(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('terms_accepted');

        $this->assertGuest();
        $this->assertDatabaseMissing('audit_logs', [
            'user_id' => $user->id,
            'action' => AuditAction::TermsAccepted->value,
        ]);
    }

    public function test_successful_login_logs_terms_acceptance_in_audit(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $this->post('/login', $this->loginPayload($user))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $log = AuditLog::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($log);
        $this->assertSame(AuditAction::TermsAccepted, $log->action);
        $this->assertSame('users', $log->table_name);
        $this->assertSame($user->id, $log->row_id);
        $this->assertSame(config('acalis.terms.version'), $log->new_values['disclaimer_version']);
        $this->assertSame('aceptacion_uso_informacion', $log->new_values['event']);
        $this->assertNotNull($log->new_values['accepted_at_display']);
        $this->assertNotNull($log->new_values['browser']);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
    }

    public function test_remember_me_issues_persistent_cookie(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::Admin->value);

        $response = $this->post('/login', $this->loginPayload($user, [
            'remember' => '1',
        ]));

        $response->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->remember_token);

        $cookieName = auth()->guard()->getRecallerName();
        $response->assertCookie($cookieName);
    }

    /** @param  array<string, mixed>  $overrides */
    private function loginPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'email' => $user->email,
            'password' => 'password',
            'terms_accepted' => '1',
        ], $overrides);
    }
}
