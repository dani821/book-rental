<?php

declare(strict_types=1);

namespace App\Exceptions\Rentals;

use App\Exceptions\DomainException;

final class RentalAlreadyFinishedException extends DomainException
{
    public function __construct(string $message = 'This rental has already been completed.')
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 409;
    }

    public function title(): string
    {
        return 'Rental Already Finished';
    }
}
