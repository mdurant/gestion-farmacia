<?php

namespace Tests\Unit;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncryptedAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_sensitive_fields_are_encrypted_at_rest(): void
    {
        $resident = Resident::query()->create([
            'rut' => '18.765.432-1',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);

        $this->assertSame('Juan', $resident->fresh()->first_name);
        $this->assertNotSame('Juan', $resident->getRawOriginal('first_name'));
    }

    public function test_user_rut_is_encrypted_at_rest(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Silva',
            'rut' => '16.234.567-8',
        ]);

        $this->assertSame('16.234.567-8', $user->fresh()->rut);
        $this->assertNotSame('16.234.567-8', $user->getRawOriginal('rut'));
    }
}
