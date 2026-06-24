<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PdfExportBrandingTest extends TestCase
{
    public function test_pdf_export_template_shows_system_name_version_and_report_title(): void
    {
        config([
            'acalis.app.name' => 'Acalis Pharma',
            'acalis.app.version' => '2.4.1',
        ]);

        $html = View::make('reports.exports.table', [
            'systemName' => config('acalis.app.name'),
            'systemVersion' => config('acalis.app.version'),
            'title' => 'Kardex de movimientos',
            'headers' => ['Fecha', 'Fármaco'],
            'rows' => [],
            'filters' => null,
            'generatedAt' => now()->timezone('America/Santiago'),
        ])->render();

        $this->assertStringContainsString('Acalis Pharma · v2.4.1', $html);
        $this->assertStringContainsString('Kardex de movimientos', $html);
        $this->assertStringContainsString('Versión 2.4.1', $html);
        $this->assertStringContainsString('pdf-footer', $html);
    }
}
