<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Enums\AuditAction;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Support\Facades\Request;

class TermsAcceptanceService
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function logAcceptance(User $user, string $disclaimerVersion): void
    {
        $acceptedAt = now()->timezone('America/Santiago');

        $this->auditLogRepository->create([
            'user_id' => $user->id,
            'action' => AuditAction::TermsAccepted->value,
            'table_name' => 'users',
            'row_id' => $user->id,
            'old_values' => null,
            'new_values' => [
                'event' => 'aceptacion_uso_informacion',
                'disclaimer_version' => $disclaimerVersion,
                'accepted_at' => $acceptedAt->toIso8601String(),
                'accepted_at_display' => $acceptedAt->format('d/m/Y H:i:s'),
                'browser' => UserAgentParser::browser(Request::userAgent()),
                'user_agent' => Request::userAgent(),
            ],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
