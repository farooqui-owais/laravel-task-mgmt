<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiResponder;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    use ApiResponder;

    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        return $this->successResponse(new UserCollection($this->userService->listUsers()));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return $this->createdResponse(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->updateUser($user, $request->validated());

        return $this->successResponse(new UserResource($updated), 'User updated successfully.');
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        // Self-delete is a business rule violation, not an authorization failure.
        // The Policy already allows admins through via before(); we guard here
        // so the response is 422 Unprocessable (not 403 Forbidden).
        if ($user->id === auth()->id()) {
            return $this->errorResponse('You cannot delete your own account.', 422);
        }

        $this->userService->deleteUser($user);

        return $this->noContentResponse();
    }
}
