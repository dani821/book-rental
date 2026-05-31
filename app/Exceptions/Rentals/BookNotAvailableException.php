<?php

declare(strict_types=1);

namespace App\Exceptions\Rentals;

use App\Exceptions\DomainException;

final class BookNotAvailableException extends DomainException
{
    public function __construct(string $message = 'This book has no available copies to rent.')
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 409;
    }

    public function title(): string
    {
        return 'Book Not Available';
    }
}
