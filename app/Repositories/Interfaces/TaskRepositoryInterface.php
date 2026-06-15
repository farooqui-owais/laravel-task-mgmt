<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Task;

    public function create(array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): bool;

    public function paginateForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
