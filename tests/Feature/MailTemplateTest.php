<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Enums\UserRole;
use App\Models\Batch;
use App\Models\Drug;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\ControlledDrugAuthorizationNotification;
use App\Notifications\HighValueWasteAlert;
use App\Notifications\InventoryMovementNotification;
use App\Notifications\ResidentRegisteredNotification;
use App\Notifications\UserCreatedNotification;
use App\Notifications\UserStatusChangedNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class MailTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_operational_mail_templates_render_html(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $user->assignRole(UserRole::Admin->value);

        $drug = Drug::query()->create(['code' => 'FAR-T', 'name' => 'Paracetamol']);
        $pharmacy = Pharmacy::query()->create(['code' => 'PH-T', 'name' => 'Central', 'type' => 'bodega_central']);
        $batch = Batch::query()->create([
            'drug_id' => $drug->id,
            'pharmacy_id' => $pharmacy->id,
            'batch_number' => 'L-T',
            'expiration_date' => now()->addMonths(3),
            'quantity' => 10,
            'unit_cost' => 100,
        ]);

        $movement = InventoryMovement::query()->create([
            'movement_type' => MovementType::Entry,
            'pharmacy_id' => $pharmacy->id,
            'batch_id' => $batch->id,
            'drug_id' => $drug->id,
            'cost_center_id' => \App\Models\CostCenter::query()->create(['code' => 'CC-T', 'name' => 'Piso 1'])->id,
            'user_id' => $user->id,
            'quantity' => 10,
            'unit_cost' => 100,
            'total_value' => 1000,
            'reason' => 'Entrada',
            'movement_at' => now(),
        ]);

        $resident = Resident::query()->create([
            'rut' => '11.111.111-2',
            'first_name' => 'Ana',
            'last_name' => 'Residente',
        ]);

        $notifications = [
            new InventoryMovementNotification($movement),
            new HighValueWasteAlert($movement),
            new UserCreatedNotification($user, $user),
            new UserStatusChangedNotification($user, 'deactivated', $user),
            new ResidentRegisteredNotification($resident, $user),
            new ControlledDrugAuthorizationNotification($batch, $drug, $user),
        ];

        foreach ($notifications as $notification) {
            $mail = $notification->toMail($user);
            $html = View::make($mail->view, $mail->data())->render();

            $this->assertStringContainsString('Acalis Pharma', $html);
            $this->assertStringContainsString('#7367f0', $html);
            $this->assertStringContainsString('Gestión farmacéutica', $html);
        }
    }

    public function test_otp_mail_template_renders_code(): void
    {
        $user = User::factory()->pendingActivation()->create();
        $mail = (new \App\Notifications\UserActivationOtpNotification('123456'))->toMail($user);
        $html = View::make($mail->view, $mail->data())->render();

        $this->assertStringContainsString('123456', $html);
        $this->assertStringContainsString('Código de activación', $html);
    }

    public function test_auth_mail_template_renders_html(): void
    {
        $html = View::make('mail.auth.action', [
            'headline' => 'Restablezca su contraseña',
            'greeting' => 'Hola Administrador',
            'intro' => 'Recibimos una solicitud para restablecer su contraseña.',
            'actionUrl' => 'https://example.com/reset',
            'actionLabel' => 'Restablecer contraseña',
            'footnote' => 'Si no solicitó este cambio, ignore el correo.',
            'tone' => 'primary',
            'preheader' => 'Restablecer contraseña',
            'appName' => 'Acalis Pharma',
            'appUrl' => 'https://example.com',
            'details' => [],
        ])->render();

        $this->assertStringContainsString('Restablecer contraseña', $html);
        $this->assertStringContainsString('linear-gradient(135deg,#7367f0', $html);
    }
}
