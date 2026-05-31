<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Users\CreateUserAction;
use App\Models\User;

class RegisterUserAction
{
    public function __construct(private CreateUserAction $createUserAction) {}

    /**
     * @return array{user: User, token: string}
     */
    public function handle(string $name, string $email, string $password): array
    {
        $user = $this->createUserAction->handle(
            name: $name,
            email: $email,
            password: $password,
        );

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
