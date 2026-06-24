<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\RealtimeRecipients;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationRecipientsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        config(['acalis.demo.notification_email' => 'acalisnotificaciones@gmail.com']);
    }

    public function test_unique_mailboxes_collapses_demo_users_to_one_recipient(): void
    {
        foreach (['admin', 'director', 'jefe'] as $suffix) {
            $user = User::factory()->create([
                'email' => "acalisnotificaciones+{$suffix}@gmail.com",
                'is_active' => true,
                'activated_at' => now(),
                'password' => Hash::make('password'),
            ]);
            $user->assignRole(match ($suffix) {
                'admin' => UserRole::Admin->value,
                'director' => UserRole::MedicalDirector->value,
                default => UserRole::HeadNurse->value,
            });
        }

        $recipients = RealtimeRecipients::uniqueMailboxes(RealtimeRecipients::clinicalLeads());

        $this->assertCount(1, $recipients);
        $this->assertSame('acalisnotificaciones@gmail.com', $recipients->first()->routeNotificationForMail());
    }

    public function test_merge_unique_deduplicates_by_user_and_mailbox(): void
    {
        $admin = User::factory()->create([
            'email' => 'acalisnotificaciones+admin@gmail.com',
            'is_active' => true,
            'activated_at' => now(),
        ]);
        $admin->assignRole(UserRole::Admin->value);

        $director = User::factory()->create([
            'email' => 'acalisnotificaciones+director@gmail.com',
            'is_active' => true,
            'activated_at' => now(),
        ]);
        $director->assignRole(UserRole::MedicalDirector->value);

        $merged = RealtimeRecipients::mergeUnique(
            RealtimeRecipients::admins(),
            RealtimeRecipients::clinicalLeads(),
        );

        $this->assertCount(1, $merged);
        $this->assertTrue($merged->first()->is($admin) || $merged->first()->is($director));
    }
}
