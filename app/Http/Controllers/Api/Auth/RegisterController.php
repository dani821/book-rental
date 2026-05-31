<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

#[Group('Auth', weight: 1)]
class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $registerUserAction): JsonResponse
    {
        /** @var array{name: string, email: string, password: string} $validated */
        $validated = $request->validated();

        ['user' => $user, 'token' => $token] = $registerUserAction->handle(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );

        return UserResource::make($user)
            ->additional(['meta' => ['token' => $token]])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
