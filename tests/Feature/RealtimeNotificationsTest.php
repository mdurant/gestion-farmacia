<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\ControlledDrugAuthorizationRequested;
use App\Events\ResidentRegistered;
use App\Events\UserCreated;
use App\Events\UserStatusChanged;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\ControlledDrugAuthorizationNotification;
use App\Notifications\InventoryMovementNotification;
use App\Notifications\ResidentRegisteredNotification;
use App\Notifications\UserCreatedNotification;
use App\Notifications\UserStatusChangedNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RealtimeNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_created_notifies_admins(): void
    {
        Notification::fake();

        $admin = $this->seedUser(UserRole::Admin, 'admin@acalis-pharma.cl');
        $actor = $this->seedUser(UserRole::MedicalDirector, 'director@acalis-pharma.cl');

        $newUser = User::factory()->create([
            'email' => 'nuevo@acalis-pharma.cl',
            'role' => UserRole::NursingTechnician,
            'is_active' => true,
        ]);
        $newUser->assignRole(UserRole::NursingTechnician->value);

        UserCreated::dispatch($newUser, $actor);

        Notification::assertSentTo($admin, UserCreatedNotification::class);
    }

    public function test_user_status_changed_notifies_leads(): void
    {
        Notification::fake();

        $admin = $this->seedUser(UserRole::Admin, 'admin@acalis-pharma.cl');
        $director = $this->seedUser(UserRole::MedicalDirector, 'director@acalis-pharma.cl');
        $target = $this->seedUser(UserRole::NursingTechnician, 'tens@acalis-pharma.cl');

        UserStatusChanged::dispatch($target, 'deactivated', $admin);

        Notification::assertSentTo($admin, UserStatusChangedNotification::class);
        Notification::assertSentTo($director, UserStatusChangedNotification::class);
    }

    public function test_resident_registered_notifies_clinical_leads(): void
    {
        Notification::fake();

        $director = $this->seedUser(UserRole::MedicalDirector, 'director@acalis-pharma.cl');
        $resident = Resident::query()->create([
            'rut' => '11.222.333-4',
            'first_name' => 'Ana',
            'last_name' => 'Prueba',
        ]);

        ResidentRegistered::dispatch($resident, $director);

        Notification::assertSentTo($director, ResidentRegisteredNotification::class);
    }

    public function test_controlled_drug_request_notifies_directors(): void
    {
        Notification::fake();

        $director = $this->seedUser(UserRole::MedicalDirector, 'director@acalis-pharma.cl');
        $tens = $this->seedUser(UserRole::NursingTechnician, 'tens@acalis-pharma.cl');
        $drug = Drug::query()->create([
            'code' => 'FAR-CTRL',
            'name' => 'Controlado',
            'is_controlled' => true,
        ]);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => \App\Models\Pharmacy::query()->create([
                'code' => 'PH-RT',
                'name' => 'Central',
                'type' => 'bodega_central',
            ])->id,
            'batch_number' => 'L-RT',
            'expiration_date' => now()->addMonths(3),
            'quantity' => 10,
            'unit_cost' => 100,
        ]);

        ControlledDrugAuthorizationRequested::dispatch($batch, $drug, $tens);

        Notification::assertSentTo($director, ControlledDrugAuthorizationNotification::class);
    }

    public function test_inventory_notification_supports_broadcast_channel(): void
    {
        $movement = new \App\Models\InventoryMovement([
            'movement_type' => \App\Enums\MovementType::Entry,
            'quantity' => 10,
            'total_value' => 1000,
        ]);

        $notification = new InventoryMovementNotification($movement);

        $channels = $notification->via(User::factory()->make());

        $this->assertContains('broadcast', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_user_service_dispatches_created_event(): void
    {
        Event::fake([UserCreated::class]);

        $admin = $this->seedUser(UserRole::Admin, 'admin@acalis-pharma.cl');
        $this->actingAs($admin);

        $this->post(route('users.store'), [
            'first_name' => 'Nuevo',
            'last_name' => 'Usuario',
            'rut' => '12.345.678-5',
            'email' => 'nuevo.usuario@acalis-pharma.cl',
            'role' => UserRole::NursingTechnician->value,
        ])->assertRedirect();

        Event::assertDispatched(UserCreated::class);
    }

    private function seedUser(UserRole $role, string $email): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'role' => $role,
            'is_active' => true,
        ]);
        $user->assignRole($role->value);

        return $user;
    }
}
