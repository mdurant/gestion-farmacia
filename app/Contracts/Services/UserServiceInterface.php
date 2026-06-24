<?php

namespace App\Contracts\Services;

use App\Models\User;

interface UserServiceInterface
{
    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function deactivate(User $user): User;

    public function activate(User $user): User;

    public function delete(User $user): void;

    public function restore(int $userId): User;
}
