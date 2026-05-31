<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Enums\UserRole;
use App\Exceptions\Users\UserHasActiveRentalsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

#[Group('Users', weight: 2)]
class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::query()->paginate())->response();
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUserAction): JsonResponse
    {
        $this->authorize('create', User::class);

        /** @var array{name: string, email: string, password: string, role?: string} $validated */
        $validated = $request->validated();

        $user = $createUserAction->handle(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
            role: isset($validated['role']) ? UserRole::from($validated['role']) : UserRole::Member,
        );

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return UserResource::make($user)->response();
    }

    /**
     * @throws UserHasActiveRentalsException
     */
    public function destroy(User $user, DeleteUserAction $deleteUserAction): Response
    {
        $this->authorize('delete', $user);

        $deleteUserAction->handle($user);

        return response()->noContent();
    }
}
