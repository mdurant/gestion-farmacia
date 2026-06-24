<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccessLogService;
use App\Support\ReportExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccessLogController extends Controller
{
    public function __construct(
        private readonly AccessLogService $accessLog,
    ) {}

    public function exportProfile(Request $request, string $format): Response
    {
        return $this->exportForUser($request->user(), $format, 'mi-auditoria-acceso');
    }

    public function exportUser(Request $request, User $user, string $format): Response
    {
        $this->authorize('view', $user);

        return $this->exportForUser($user, $format, 'auditoria-acceso-'.$user->id);
    }

    private function exportForUser(User $user, string $format, string $slug): Response
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        [$headers, $rows] = $this->accessLog->exportDataForUser($user);
        $title = 'Auditoría de acceso — '.$user->display_name;
        $filename = $slug.'-'.now()->format('Y-m-d').'.'.$format;

        if ($format === 'csv') {
            return ReportExporter::csv($filename, $headers, $rows);
        }

        return ReportExporter::pdf($filename, 'reports.exports.table', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $rows,
            'filters' => null,
            'generatedAt' => now()->timezone('America/Santiago'),
        ]);
    }
}
