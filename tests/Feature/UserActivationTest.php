<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserActivationChallenge;
use App\Notifications\UserActivationOtpNotification;
use App\Services\UserActivationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_create_user_sends_otp_and_leaves_account_pending(): void
    {
        Notification::fake();

        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'first_name' => 'Pedro',
                'last_name' => 'Soto',
                'rut' => '12.345.678-5',
                'email' => 'pedro.soto@acalis-pharma.cl',
                'role' => UserRole::HeadNurse->value,
            ])
            ->assertRedirect();

        $user = User::query()->where('email', 'pedro.soto@acalis-pharma.cl')->firstOrFail();

        $this->assertFalse($user->is_active);
        $this->assertNull($user->activated_at);
        $this->assertDatabaseHas('user_activation_challenges', ['user_id' => $user->id]);

        Notification::assertSentTo($user, UserActivationOtpNotification::class);
    }

    public function test_pending_user_cannot_login(): void
    {
        $user = User::factory()->pendingActivation()->create([
            'email' => 'pendiente@acalis-pharma.cl',
            'password' => Hash::make('Password1!'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password1!',
            'terms_accepted' => '1',
        ])->assertSessionHasErrors('email');
    }

    public function test_user_can_complete_activation_with_valid_otp(): void
    {
        Notification::fake();

        $user = User::factory()->pendingActivation()->create([
            'email' => 'nueva@acalis-pharma.cl',
        ]);
        $user->assignRole(UserRole::NursingTechnician->value);

        $this->post(route('activation.send'), ['email' => $user->email])
            ->assertRedirect(route('activation.verify.form'));

        $plainCode = '123456';
        $challenge = UserActivationChallenge::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $challenge->update(['code_hash' => Hash::make($plainCode)]);

        $this->withSession(['activation.email' => $user->email])
            ->post(route('activation.verify'), ['code' => $plainCode])
            ->assertRedirect(route('activation.password.form'));

        $this->withSession([
            'activation.email' => $user->email,
            'activation.user_id' => $user->id,
            'activation.verified_at' => now()->timestamp,
        ])->post(route('activation.complete'), [
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertRedirect(route('login'));

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->activated_at);
        $this->assertNotNull($user->email_verified_at);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password1!',
            'terms_accepted' => '1',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_activation_request_does_not_reveal_unknown_email(): void
    {
        Notification::fake();

        $this->post(route('activation.send'), ['email' => 'noexiste@acalis-pharma.cl'])
            ->assertRedirect(route('activation.verify.form'))
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_admin_can_resend_activation_code(): void
    {
        Notification::fake();

        $admin = $this->adminUser();
        $user = User::factory()->pendingActivation()->create();
        $user->assignRole(UserRole::NursingTechnician->value);

        $this->actingAs($admin)
            ->post(route('users.resend-activation', $user))
            ->assertRedirect();

        Notification::assertSentTo($user, UserActivationOtpNotification::class);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }
}
