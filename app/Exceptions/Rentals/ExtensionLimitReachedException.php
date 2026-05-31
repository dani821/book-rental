<?php

declare(strict_types=1);

namespace App\Exceptions\Rentals;

use App\Exceptions\DomainException;

final class ExtensionLimitReachedException extends DomainException
{
    public function __construct(string $message = 'This rental has reached its maximum number of extensions.')
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 422;
    }

    public function title(): string
    {
        return 'Extension Limit Reached';
    }
}
