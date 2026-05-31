<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Auth', weight: 1)]
class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginUserAction $loginUserAction): JsonResponse
    {
        /** @var array{email: string, password: string} $validated */
        $validated = $request->validated();

        ['user' => $user, 'token' => $token] = $loginUserAction->handle(
            email: $validated['email'],
            password: $validated['password'],
        );

        return UserResource::make($user)
            ->additional(['meta' => ['token' => $token]])
            ->response();
    }
}
