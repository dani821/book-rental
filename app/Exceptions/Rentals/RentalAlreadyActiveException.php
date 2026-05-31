<?php

declare(strict_types=1);

namespace App\Exceptions\Rentals;

use App\Exceptions\DomainException;

final class RentalAlreadyActiveException extends DomainException
{
    public function __construct(string $message = 'You already have an active rental for this book.')
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 409;
    }

    public function title(): string
    {
        return 'Rental Already Active';
    }
}
