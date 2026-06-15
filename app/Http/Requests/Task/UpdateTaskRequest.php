<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task $task */
        $task = $this->route('task');

        return $this->user()->can('update', $task);
    }

    public function rules(): array
    {
        $user = $this->user();

        // Employees may only update the status field
        if ($user->hasRole('employee')) {
            return [
                'status' => ['required', new Enum(TaskStatus::class)],
            ];
        }

        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['sometimes', new Enum(TaskPriority::class)],
            'status'      => ['sometimes', new Enum(TaskStatus::class)],
            'due_date'    => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
