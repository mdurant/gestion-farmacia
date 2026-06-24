<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\DemoAccounts;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        config([
            'acalis.demo.notification_email' => 'acalisnotificaciones@gmail.com',
            'acalis.demo.enabled' => true,
        ]);
    }

    public function test_demo_login_emails_use_gmail_plus_aliases(): void
    {
        $emails = DemoAccounts::loginEmails();

        $this->assertCount(4, $emails);
        $this->assertContains('acalisnotificaciones+admin@gmail.com', $emails);
        $this->assertContains('acalisnotificaciones+director@gmail.com', $emails);
    }

    public function test_demo_users_route_notifications_to_shared_inbox(): void
    {
        $user = User::factory()->create([
            'email' => 'acalisnotificaciones+tens@gmail.com',
            'role' => UserRole::NursingTechnician,
            'password' => Hash::make('password'),
            'is_active' => true,
            'activated_at' => now(),
        ]);
        $user->assignRole(UserRole::NursingTechnician->value);

        $this->assertTrue(DemoAccounts::isDemoUser($user));
        $this->assertSame('acalisnotificaciones@gmail.com', $user->routeNotificationForMail());
    }

    public function test_non_demo_users_keep_their_email_for_notifications(): void
    {
        $user = User::factory()->create([
            'email' => 'personal@hospital.cl',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->assertFalse(DemoAccounts::isDemoUser($user));
        $this->assertSame('personal@hospital.cl', $user->routeNotificationForMail());
    }
}
