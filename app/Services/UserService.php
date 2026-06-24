<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Enums\UserRole;
use App\Events\UserCreated;
use App\Events\UserStatusChanged;
use App\Models\User;
use App\Services\UserActivationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserActivationService $activationService,
    ) {}

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $role = UserRole::from($data['role']);

            $user = $this->userRepository->create([
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'rut' => $data['rut'],
                'email' => Str::lower(trim($data['email'])),
                'password' => Hash::make(Str::random(64)),
                'role' => $role,
                'is_active' => false,
                'activated_at' => null,
            ]);

            $user->syncRoles([$role->value]);

            UserCreated::dispatch($user, Auth::user());

            $this->activationService->issueChallenge($user);

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $payload = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'rut' => $data['rut'],
                'email' => $data['email'],
                'name' => trim("{$data['first_name']} {$data['last_name']}"),
                'is_active' => $data['is_active'] ?? $user->is_active,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            if (isset($data['role'])) {
                $role = UserRole::from($data['role']);
                $payload['role'] = $role;
                $user->syncRoles([$role->value]);
            }

            return $this->userRepository->update($user, $payload);
        });
    }

    public function deactivate(User $user): User
    {
        $user = $this->userRepository->update($user, ['is_active' => false]);
        UserStatusChanged::dispatch($user, 'deactivated', Auth::user());

        return $user;
    }

    public function activate(User $user): User
    {
        if ($user->isPendingActivation()) {
            throw new \InvalidArgumentException('El usuario debe completar la activación por correo antes de habilitar el acceso.');
        }

        $user = $this->userRepository->update($user, ['is_active' => true]);
        UserStatusChanged::dispatch($user, 'activated', Auth::user());

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
        UserStatusChanged::dispatch($user, 'deleted', Auth::user());
    }

    public function restore(int $userId): User
    {
        $user = User::query()->onlyTrashed()->findOrFail($userId);
        $user->restore();
        $user = $user->fresh();
        UserStatusChanged::dispatch($user, 'restored', Auth::user());

        return $user;
    }
}
