<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

abstract class DomainException extends Exception
{
    abstract public function status(): int;

    abstract public function title(): string;
}
