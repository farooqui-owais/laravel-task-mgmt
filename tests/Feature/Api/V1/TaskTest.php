<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear the Spatie permission cache
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Explicitly create the roles in the test database
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'employee']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');
    }

    /** @test */
    public function admin_can_create_a_task_successfully(): void
    {
        $payload = [
            'title' => 'Test Task',
            'description' => 'Description here',
            'priority' => 'high',
            'status' => 'pending',
            'due_date' => now()->addDays(5)->toDateTimeString(),
            'assigned_to' => $this->employee->id,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Test Task');

        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
    }

    /** @test */
    public function creation_fails_if_due_date_is_in_the_past(): void
    {
        $payload = [
            'title' => 'Past Task',
            'priority' => 'low',
            'due_date' => '2020-01-01 00:00:00', // Past date
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/tasks', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    /** @test */
    public function employee_cannot_delete_any_task(): void
    {
        $task = Task::factory()->create(['assigned_to' => $this->employee->id]);

        $response = $this->actingAs($this->employee)
            ->deleteJson("/api/v1/tasks/{$task->id}");
        $response->assertStatus(403);
    }
}