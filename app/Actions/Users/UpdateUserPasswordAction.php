<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

class UpdateUserPasswordAction
{
    public function handle(User $user, string $newPassword, ?int $keepTokenId = null): void
    {
        $user->update(['password' => $newPassword]);

        $tokens = $user->tokens();

        if ($keepTokenId !== null) {
            $tokens->whereKeyNot($keepTokenId);
        }

        $tokens->delete();
    }
}
