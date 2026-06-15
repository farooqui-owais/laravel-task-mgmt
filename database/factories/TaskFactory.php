<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'priority'    => $this->faker->randomElement(TaskPriority::cases())->value,
            'status'      => $this->faker->randomElement(TaskStatus::cases())->value,
            'due_date'    => $this->faker->dateTimeBetween('now', '+30 days'),
            'assigned_to' => User::factory(),
            'created_by'  => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => TaskStatus::Pending->value]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => TaskStatus::InProgress->value]);
    }

    public function completed(): static
    {
        return $this->state(['status' => TaskStatus::Completed->value]);
    }

    public function highPriority(): static
    {
        return $this->state(['priority' => TaskPriority::High->value]);
    }
}
