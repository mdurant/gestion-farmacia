<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\HttpErrorCatalog;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HttpErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_catalog_includes_common_status_codes(): void
    {
        $catalog = HttpErrorCatalog::all();

        foreach ([400, 401, 403, 404, 407, 408, 419, 422, 429, 500, 502, 503, 504] as $code) {
            $this->assertArrayHasKey($code, $catalog, "Missing HTTP {$code} in catalog");
        }
    }

    public function test_not_found_renders_custom_error_page(): void
    {
        $this->get('/ruta-que-no-existe-acalis')
            ->assertNotFound()
            ->assertSee('404', false)
            ->assertSee('No encontrado', false)
            ->assertSee('Acalis Pharma', false);
    }

    public function test_forbidden_renders_custom_error_page(): void
    {
        $tens = User::factory()->create(['is_active' => true]);
        $tens->assignRole(UserRole::NursingTechnician->value);

        $this->actingAs($tens)
            ->get(route('users.index'))
            ->assertForbidden()
            ->assertSee('403', false)
            ->assertSee('Acceso denegado', false);
    }

    public function test_http_error_gallery_is_available_in_local(): void
    {
        $this->get(route('dev.http-errors.index'))
            ->assertOk()
            ->assertSee('Galería de códigos HTTP', false)
            ->assertSee('504', false);

        $this->get(route('dev.http-errors.preview', 504))
            ->assertStatus(504)
            ->assertSee('Gateway Timeout', false);
    }

    public function test_unknown_status_code_uses_fallback_copy(): void
    {
        $this->get('/dev/errores-http/499')
            ->assertStatus(404);
    }
}
