<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BookRental;
use App\Models\User;

class BookRentalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BookRental $rental): bool
    {
        return $this->owns(user: $user, rental: $rental);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BookRental $rental): bool
    {
        return $this->owns(user: $user, rental: $rental);
    }

    private function owns(User $user, BookRental $rental): bool
    {
        return $user->id === $rental->user_id || $user->isAdmin();
    }
}
