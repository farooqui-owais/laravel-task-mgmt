<?php

declare(strict_types=1);

namespace Tests\Feature\Role;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin    = User::factory()->create()->assignRole('admin');
        $this->manager  = User::factory()->create()->assignRole('manager');
        $this->employee = User::factory()->create()->assignRole('employee');
    }

    // ─── Admin Access ──────────────────────────────────────────────────────────

    public function test_admin_can_list_users(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertOk();
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/users', [
                'name'     => 'New User',
                'email'    => 'newuser@example.com',
                'password' => 'Password123!',
                'role'     => 'employee',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'newuser@example.com');
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create()->assignRole('employee');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/users/{$user->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create()->assignRole('employee');

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/users/{$this->admin->id}")
            ->assertUnprocessable();
    }

    // ─── Manager Access ────────────────────────────────────────────────────────

    public function test_manager_cannot_list_users(): void
    {
        $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertForbidden();
    }

    public function test_manager_cannot_create_user(): void
    {
        $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/v1/users', [
                'name'     => 'Sneaky User',
                'email'    => 'sneaky@example.com',
                'password' => 'Password123!',
                'role'     => 'employee',
            ])
            ->assertForbidden();
    }

    public function test_manager_cannot_delete_user(): void
    {
        $user = User::factory()->create()->assignRole('employee');

        $this->actingAs($this->manager, 'sanctum')
            ->deleteJson("/api/v1/users/{$user->id}")
            ->assertForbidden();
    }

    public function test_manager_can_view_all_tasks(): void
    {
        Task::factory()->count(5)->create();

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/v1/tasks');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(5, count($response->json('data.data')));
    }

    public function test_manager_can_assign_task_to_employee(): void
    {
        $task = Task::factory()->create(['assigned_to' => null]);

        $this->actingAs($this->manager, 'sanctum')
            ->putJson("/api/v1/tasks/{$task->id}", [
                'assigned_to' => $this->employee->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.assigned_to.id', $this->employee->id);
    }

    // ─── Employee Restrictions ─────────────────────────────────────────────────

    public function test_employee_cannot_list_users(): void
    {
        $this->actingAs($this->employee, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertForbidden();
    }

    public function test_employee_cannot_create_users(): void
    {
        $this->actingAs($this->employee, 'sanctum')
            ->postJson('/api/v1/users', [
                'name'     => 'Fake User',
                'email'    => 'fake@example.com',
                'password' => 'Password123!',
                'role'     => 'admin',
            ])
            ->assertForbidden();
    }

    public function test_employee_cannot_view_tasks_not_assigned_to_them(): void
    {
        $otherTask = Task::factory()->create(['assigned_to' => $this->manager->id]);

        $this->actingAs($this->employee, 'sanctum')
            ->getJson("/api/v1/tasks/{$otherTask->id}")
            ->assertForbidden();
    }

    public function test_employee_cannot_delete_tasks(): void
    {
        $task = Task::factory()->create(['assigned_to' => $this->employee->id]);

        $this->actingAs($this->employee, 'sanctum')
            ->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertForbidden();
    }

    public function test_employee_cannot_change_task_title(): void
    {
        $task = Task::factory()->create(['assigned_to' => $this->employee->id]);

        // Employee update rules only allow 'status'; extra fields are stripped/rejected
        $response = $this->actingAs($this->employee, 'sanctum')
            ->putJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Hacked title',
            ]);

        // Should be unprocessable (status required for employees, title not allowed)
        $response->assertUnprocessable();
    }
}
