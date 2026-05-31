<?php

declare(strict_types=1);

namespace App\Exceptions\Users;

use App\Exceptions\DomainException;

final class UserHasActiveRentalsException extends DomainException
{
    public function __construct(string $message = 'This user cannot be deleted while they have active rentals.')
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 409;
    }

    public function title(): string
    {
        return 'User Has Active Rentals';
    }
}
