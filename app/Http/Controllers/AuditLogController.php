<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        return view('audit.index', [
            'logs' => $this->auditLogRepository->paginate([
                'table_name' => $request->input('table_name', 'users'),
                'action' => $request->input('action'),
                'row_id' => $request->input('row_id'),
                'user_id' => $request->input('user_id'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ]),
            'actions' => AuditAction::cases(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }
}
