<?php

declare(strict_types=1);

namespace App\Enums;

enum BookGenre: string
{
    case Fiction = 'fiction';
    case NonFiction = 'non_fiction';
    case Science = 'science';
    case History = 'history';
    case Fantasy = 'fantasy';
    case Biography = 'biography';
}
