<?php

namespace Tests\Feature;

use App\Enums\ResidentAccessAction;
use App\Enums\UserRole;
use App\Models\Resident;
use App\Models\ResidentAccessLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentDataGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_residents_index_redirects_to_gate_without_password_confirmation(): void
    {
        $user = $this->headNurse();

        $this->actingAs($user)
            ->get(route('residents.index'))
            ->assertRedirect(route('residents.gate.show'));
    }

    public function test_gate_page_is_accessible_and_shows_disclaimer(): void
    {
        $user = $this->headNurse();

        $this->actingAs($user)
            ->get(route('residents.gate.show'))
            ->assertOk()
            ->assertSee('Ley N° 21.719', false)
            ->assertSee('Toda actividad es auditable', false)
            ->assertSee('Contraseña institucional', false);
    }

    public function test_wrong_password_is_rejected_and_logged(): void
    {
        $user = $this->headNurse();

        $this->actingAs($user)
            ->post(route('residents.gate.confirm'), [
                'password' => 'wrong-password',
                'disclaimer_accepted' => '1',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(
            ResidentAccessLog::query()
                ->where('action', ResidentAccessAction::ModuleAccessDenied)
                ->where('user_id', $user->id)
                ->exists()
        );
    }

    public function test_correct_password_grants_access_to_residents_module(): void
    {
        $user = $this->headNurse();

        $this->confirmGate($user)
            ->assertRedirect(route('residents.index'));

        $this->actingAs($user)
            ->get(route('residents.index'))
            ->assertOk();

        $this->assertTrue(
            ResidentAccessLog::query()
                ->where('action', ResidentAccessAction::ModuleAccessGranted)
                ->where('user_id', $user->id)
                ->exists()
        );
    }

    public function test_direct_resident_show_url_is_blocked_without_gate(): void
    {
        $user = $this->headNurse();
        $resident = Resident::query()->create([
            'rut' => '1-9',
            'first_name' => 'Test',
            'last_name' => 'Resident',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('residents.show', $resident))
            ->assertRedirect(route('residents.gate.show'));
    }

    private function headNurse(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(UserRole::HeadNurse->value);

        return $user;
    }

    private function confirmGate(User $user): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)
            ->post(route('residents.gate.confirm'), [
                'password' => 'password',
                'disclaimer_accepted' => '1',
            ]);
    }
}
