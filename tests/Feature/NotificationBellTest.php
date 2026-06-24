<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\NotificationBell;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_mark_all_as_read_clears_unread_count(): void
    {
        $admin = $this->adminUser();

        $this->seedUnreadNotification($admin, 'Primera alerta');
        $this->seedUnreadNotification($admin, 'Segunda alerta');

        $this->assertSame(2, $admin->unreadNotifications()->count());

        Livewire::actingAs($admin)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 2)
            ->call('markAllAsRead')
            ->assertSet('unreadCount', 0);

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
        $this->assertSame(2, $admin->fresh()->readNotifications()->count());
    }

    public function test_mark_single_notification_as_read(): void
    {
        $admin = $this->adminUser();
        $notification = $this->seedUnreadNotification($admin, 'Alerta única');

        Livewire::actingAs($admin)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 1)
            ->call('markAsRead', $notification->id)
            ->assertSet('unreadCount', 0);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }

    private function seedUnreadNotification(User $user, string $title): \Illuminate\Notifications\DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\TestNotification',
            'data' => [
                'title' => $title,
                'message' => 'Mensaje de prueba',
                'severity' => 'info',
                'category' => 'user',
            ],
            'read_at' => null,
        ]);
    }
}
