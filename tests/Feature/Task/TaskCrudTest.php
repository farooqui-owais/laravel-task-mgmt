<?php

declare(strict_types=1);

namespace Tests\Feature\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear the Spatie permission cache
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Explicitly create the roles in the test database
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'employee']);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin    = User::factory()->create()->assignRole('admin');
        $this->manager  = User::factory()->create()->assignRole('manager');
        $this->employee = User::factory()->create()->assignRole('employee');
    }

    // ─── Task Creation ─────────────────────────────────────────────────────────

    public function test_admin_can_create_a_task(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title'       => 'Fix login bug',
                'description' => 'The login page throws a 500 error.',
                'priority'    => TaskPriority::High->value,
                'status'      => TaskStatus::Pending->value,
                'assigned_to' => $this->employee->id,
                'due_date'    => now()->addDays(7)->toDateTimeString(),
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Fix login bug')
            ->assertJsonPath('data.priority.value', 'high');

        $this->assertDatabaseHas('tasks', ['title' => 'Fix login bug']);
    }

    public function test_manager_can_create_a_task(): void
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title'    => 'Write unit tests',
                'priority' => TaskPriority::Medium->value,
            ]);

        $response->assertCreated();
    }

    public function test_employee_cannot_create_a_task(): void
    {
        $response = $this->actingAs($this->employee, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title'    => 'Unauthorised task',
                'priority' => TaskPriority::Low->value,
            ]);
        $response->dump();
        $response->assertForbidden();
    }

    public function test_task_creation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/tasks', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'priority']);
    }

    public function test_task_creation_fails_with_invalid_priority(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title'    => 'Test task',
                'priority' => 'critical', // invalid
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['priority']);
    }

    // ─── Task Reading ──────────────────────────────────────────────────────────

    public function test_admin_can_list_all_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['data', 'meta'],
            ]);
    }

    public function test_employee_can_only_see_assigned_tasks(): void
    {
        Task::factory()->count(5)->create(['assigned_to' => $this->manager->id]);
        $myTask = Task::factory()->create(['assigned_to' => $this->employee->id]);

        $response = $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/v1/tasks');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->toArray();
        $this->assertContains($myTask->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_admin_can_view_a_single_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/tasks/99999')
            ->assertNotFound();
    }

    // ─── Task Updating ─────────────────────────────────────────────────────────

    public function test_manager_can_update_any_task_field(): void
    {
        $task = Task::factory()->create(['status' => TaskStatus::Pending->value]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/v1/tasks/{$task->id}", [
                'status' => TaskStatus::InProgress->value,
                'title'  => 'Updated title',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status.value', 'in-progress')
            ->assertJsonPath('data.title', 'Updated title');
    }

    public function test_employee_can_update_status_of_own_task(): void
    {
        $task = Task::factory()->create([
            'assigned_to' => $this->employee->id,
            'status'      => TaskStatus::Pending->value,
        ]);

        $response = $this->actingAs($this->employee, 'sanctum')
            ->putJson("/api/v1/tasks/{$task->id}", [
                'status' => TaskStatus::InProgress->value,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status.value', 'in-progress');
    }

    public function test_employee_cannot_update_task_not_assigned_to_them(): void
    {
        $task = Task::factory()->create(['assigned_to' => $this->manager->id]);

        $this->actingAs($this->employee, 'sanctum')
            ->putJson("/api/v1/tasks/{$task->id}", [
                'status' => TaskStatus::Completed->value,
            ])
            ->assertForbidden();
    }

    // ─── Task Deletion ─────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_employee_cannot_delete_a_task(): void
    {
        $task = Task::factory()->create(['assigned_to' => $this->employee->id]);
         
        $this->actingAs($this->employee, 'sanctum')
            ->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertForbidden();
    }

    // ─── Task History ──────────────────────────────────────────────────────────

    public function test_task_history_is_recorded_on_status_change(): void
    {
        $task = Task::factory()->create([
            'status'      => TaskStatus::Pending->value,
            'assigned_to' => $this->employee->id,
        ]);

        $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/v1/tasks/{$task->id}", [
                'status' => TaskStatus::InProgress->value,
            ]);

        $this->assertDatabaseHas('task_histories', [
            'task_id'       => $task->id,
            'field_changed' => 'status',
            'old_value'     => TaskStatus::Pending->value,
            'new_value'     => TaskStatus::InProgress->value,
        ]);
    }
}
