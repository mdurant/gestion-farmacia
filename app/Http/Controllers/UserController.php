<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Enums\UserRole;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AccessLogService;
use App\Services\UserActivationService;
use App\Support\RequestFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserServiceInterface $userService,
        private readonly UserActivationService $activationService,
        private readonly AccessLogService $accessLog,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $baseQuery = User::query();
        if ($request->boolean('trashed')) {
            $baseQuery->onlyTrashed();
        }

        return view('users.index', [
            'users' => $this->userRepository->paginate([
                'search' => RequestFilters::optionalString($request, 'search'),
                'role' => RequestFilters::optionalString($request, 'role'),
                'is_active' => RequestFilters::optionalBoolean($request, 'is_active'),
                'trashed' => $request->boolean('trashed'),
            ]),
            'roles' => UserRole::cases(),
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->userService->create($request->validated());

        return redirect()
            ->route('users.show', $user)
            ->with('status', 'Usuario creado. Se envió un código de activación al correo registrado.');
    }

    public function resendActivation(User $user): RedirectResponse
    {
        $this->authorize('resendActivation', $user);

        if ($user->isActivated()) {
            return back()->with('status', 'La cuenta ya está activada.');
        }

        if (! $this->activationService->canResend($user)) {
            return back()->withErrors([
                'activation' => 'Espere un momento antes de reenviar el código.',
            ]);
        }

        $this->activationService->issueChallenge($user);

        return back()->with('status', 'Código de activación reenviado al correo del usuario.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $auditLogs = AuditLog::query()
            ->with('user')
            ->where('table_name', $user->getTable())
            ->where('row_id', $user->id)
            ->latest()
            ->limit(15)
            ->get();

        return view('users.show', [
            'user' => $user,
            'auditLogs' => $auditLogs,
            'accessLogs' => $this->accessLog->paginateForUser($user),
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $auditLogs = AuditLog::query()
            ->where('table_name', $user->getTable())
            ->where('row_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
            'auditLogs' => $auditLogs,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('users.show', $user)
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->userService->delete($user);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario dado de baja correctamente.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->authorize('toggleActive', $user);

        if ($user->isPendingActivation()) {
            return back()->withErrors([
                'activation' => 'El usuario debe completar la activación por correo antes de habilitar el acceso.',
            ]);
        }

        if ($user->is_active) {
            $this->userService->deactivate($user);
            $message = 'Usuario desactivado.';
        } else {
            $this->userService->activate($user);
            $message = 'Usuario reactivado.';
        }

        return back()->with('status', $message);
    }

    public function restore(int $userId): RedirectResponse
    {
        $this->authorize('restore', User::class);

        $user = $this->userService->restore($userId);

        return redirect()
            ->route('users.show', $user)
            ->with('status', 'Usuario restaurado correctamente.');
    }
}
