<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserRole;
use App\Models\User;

class CreateUserAction
{
    public function handle(string $name, string $email, string $password, UserRole $role = UserRole::Member): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ]);
    }
}
