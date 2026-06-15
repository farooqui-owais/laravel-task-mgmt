<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Admins bypass all policy checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'employee']);
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole('manager')) {
            return true;
        }

        return $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->hasRole('manager')) {
            return true;
        }

        // Employees can only update status on their own tasks
        return $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }
}
