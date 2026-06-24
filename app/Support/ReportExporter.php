<?php

namespace App\Support;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class ReportExporter
{
    /** @param list<string> $headers @param list<array<string, mixed>> $rows */
    public static function csv(string $filename, array $headers, array $rows): Response
    {
        $content = self::buildCsv($headers, $rows);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /** @param list<string> $headers @param list<array<string, mixed>> $rows */
    private static function buildCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, $headers, ';');

        foreach ($rows as $row) {
            fputcsv($handle, array_values($row), ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }

    /** @param array<string, mixed> $data */
    public static function pdf(string $filename, string $view, array $data): Response
    {
        $html = View::make($view, array_merge(self::pdfBranding(), $data))->render();

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $output = $dompdf->output();

            return response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Content-Length' => (string) strlen($output),
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        }

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.str_replace('.pdf', '.html', $filename).'"',
            'Content-Length' => (string) strlen($html),
        ]);
    }

    /** @return array{systemName: string, systemVersion: string} */
    private static function pdfBranding(): array
    {
        return [
            'systemName' => (string) config('acalis.app.name', 'Acalis Pharma'),
            'systemVersion' => (string) config('acalis.app.version', '1.0.0'),
        ];
    }
}
