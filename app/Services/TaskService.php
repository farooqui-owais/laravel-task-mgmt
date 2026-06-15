<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\User;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Enums\TaskStatus; // Assuming this enum is used for default status
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function listTasks(User $user, array $filters = []): LengthAwarePaginator
    {
        if ($user->hasRole('employee')) {
            return $this->taskRepository->paginateForUser($user, $filters);
        }

        return $this->taskRepository->paginate($filters);
    }

    public function createTask(array $data): Task
    {
        $data['status'] = $data['status'] ?? TaskStatus::Pending->value;
        return DB::transaction(function () use ($data): Task {
            $data['created_by'] = Auth::id();
            $task = $this->taskRepository->create($data);

            $this->logHistory($task, 'status', null, $task->status->value);

            if (isset($data['assigned_to'])) {
                $this->logHistory($task, 'assigned_to', null, (string) $data['assigned_to']);
                event(new TaskAssigned($task));
            }

            return $task->load(['assignee', 'creator']);
        });
    }

    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data): Task {
            $originalStatus     = $task->status?->value;
            $originalAssignedTo = $task->assigned_to;

            $updated = $this->taskRepository->update($task, $data);

            if (isset($data['status']) && $data['status'] !== $originalStatus) {
                $this->logHistory($updated, 'status', $originalStatus, $data['status']);
                event(new TaskStatusChanged($updated, $originalStatus, $data['status']));
            }

            if (isset($data['assigned_to']) && $data['assigned_to'] !== $originalAssignedTo) {
                $this->logHistory($updated, 'assigned_to', (string) $originalAssignedTo, (string) $data['assigned_to']);
                event(new TaskAssigned($updated));
            }

            return $updated;
        });
    }

    public function deleteTask(Task $task): bool
    {
        return $this->taskRepository->delete($task);
    }

    private function logHistory(Task $task, string $field, ?string $oldValue, ?string $newValue): void
    {
        TaskHistory::create([
            'task_id'       => $task->id,
            'changed_by'    => Auth::id(),
            'field_changed' => $field,
            'old_value'     => $oldValue,
            'new_value'     => $newValue,
        ]);
    }
}
