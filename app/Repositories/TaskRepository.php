<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Task::query()
            ->with(['assignee', 'creator'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))
            ->when($filters['due_date'] ?? null, fn ($q, $v) => $q->whereDate('due_date', $v))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Task
    {
        return Task::with(['assignee', 'creator', 'histories.changedBy'])->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh(['assignee', 'creator']);
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function paginateForUser(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Task::query()
            ->with(['assignee', 'creator'])
            ->where('assigned_to', $user->id)
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
