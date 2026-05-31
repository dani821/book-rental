<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\RentalStatus;
use App\Exceptions\Users\UserHasActiveRentalsException;
use App\Models\User;
use Throwable;

class DeleteUserAction
{
    /**
     * @throws UserHasActiveRentalsException
     * @throws Throwable
     */
    public function handle(User $user): void
    {
        $hasActiveRentals = $user->rentals()
            ->where('status', RentalStatus::Active)
            ->exists();

        if ($hasActiveRentals) {
            throw new UserHasActiveRentalsException;
        }

        $user->delete();
    }
}
