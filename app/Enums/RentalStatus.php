<?php

declare(strict_types=1);

namespace App\Enums;

enum RentalStatus: string
{
    case Active = 'active';
    case Completed = 'completed';

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
