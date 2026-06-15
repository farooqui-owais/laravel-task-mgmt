<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiResponder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskCollection;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    use ApiResponder;

    public function __construct(
        private readonly TaskService $taskService,
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Task::class);

        $tasks = $this->taskService->listTasks(
            $request->user(),
            $request->only(['status', 'priority', 'assigned_to', 'due_date']),
        );

        return $this->successResponse(new TaskCollection($tasks));
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return $this->createdResponse(new TaskResource($task));
    }

    public function show(int $id): JsonResponse
    {
        $task = $this->taskRepository->findById($id);

        if ($task === null) {
            return $this->errorResponse('Task not found.', 404);
        }

        Gate::authorize('view', $task);

        return $this->successResponse(new TaskResource($task));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updated = $this->taskService->updateTask($task, $request->validated());

        return $this->successResponse(new TaskResource($updated), 'Task updated successfully.');
    }

    public function destroy(Task $task): JsonResponse
    {
        Gate::authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return $this->noContentResponse();
    }
}
