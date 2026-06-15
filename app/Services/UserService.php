<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function listUsers(): LengthAwarePaginator
    {
        return $this->userRepository->paginate();
    }

    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $role = $data['role'] ?? 'employee';
            unset($data['role']);

            $user = $this->userRepository->create($data);
            $user->assignRole($role);

            return $user->load('roles');
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $role = $data['role'] ?? null;
            unset($data['role']);

            $updated = $this->userRepository->update($user, $data);

            if ($role !== null) {
                $updated->syncRoles([$role]);
            }

            return $updated->load('roles');
        });
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }
}
