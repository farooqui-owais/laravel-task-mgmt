<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TaskRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new TaskRepository();
    }

    /** @test */
    public function it_can_filter_tasks_by_status(): void
    {
        Task::factory()->create(['status' => 'pending']);
        Task::factory()->create(['status' => 'completed']);

        $filters = ['status' => 'pending'];
        $results = $this->repository->paginate($filters);

        $this->assertEquals(1, $results->total());
        $this->assertEquals('pending', $results->first()->status->value);
    }
}