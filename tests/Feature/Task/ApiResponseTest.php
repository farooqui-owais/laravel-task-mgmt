<?php

declare(strict_types=1);

namespace Tests\Feature\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear the Spatie permission cache
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Explicitly create the roles in the test database
        Role::create(['name' => 'admin']);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole('admin');
    }

    // ─── Success Response Shape ────────────────────────────────────────────────

    public function test_task_list_response_has_correct_structure(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'priority' => ['value', 'label'],
                            'status'   => ['value', 'label'],
                            'due_date',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);
    }

    public function test_single_task_response_includes_history(): void
    {
        $task = Task::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'histories'],
            ]);
    }

    public function test_created_task_returns_201_status(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title'    => 'New task',
                'priority' => TaskPriority::Low->value,
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);
    }

    public function test_deleted_task_returns_204_no_content(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/tasks/{$task->id}")
            ->assertStatus(204);
    }

    // ─── Validation Error Response Shape ──────────────────────────────────────

    public function test_validation_errors_have_standard_shape(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/tasks', []);

        $response->assertUnprocessable()
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_invalid_enum_returns_validation_error(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/tasks', [
                'title'    => 'Test',
                'priority' => 'extreme',   // not a valid TaskPriority
                'status'   => 'archived',  // not a valid TaskStatus
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['priority', 'status']);
    }

    // ─── Unauthorized Response ─────────────────────────────────────────────────

    public function test_missing_token_returns_401(): void
    {
        $this->getJson('/api/v1/tasks')->assertUnauthorized();
        $this->postJson('/api/v1/tasks', [])->assertUnauthorized();
    }

    public function test_forbidden_action_returns_403(): void
    {
        $employee = User::factory()->create()->assignRole('employee');

        $this->actingAs($employee, 'sanctum')
            ->getJson('/api/v1/users')
            ->assertForbidden();
    }

    public function test_nonexistent_resource_returns_404(): void
    {
        $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/tasks/999999')
            ->assertNotFound()
            ->assertJson(['success' => false]);
    }
}
