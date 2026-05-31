<?php

declare(strict_types=1);

namespace App\Exceptions\Books;

use App\Exceptions\DomainException;

final class BookHasActiveRentalsException extends DomainException
{
    public function __construct(string $message = 'This book cannot be deleted while it has active rentals.')
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 409;
    }

    public function title(): string
    {
        return 'Book Has Active Rentals';
    }
}
