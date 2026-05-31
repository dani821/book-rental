<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Enums\RentalStatus;
use App\Exceptions\Books\BookHasActiveRentalsException;
use App\Models\Book;

class DeleteBookAction
{
    /**
     * @throws BookHasActiveRentalsException
     */
    public function handle(Book $book): void
    {
        $hasActiveRentals = $book->rentals()
            ->where('status', RentalStatus::Active)
            ->exists();

        if ($hasActiveRentals) {
            throw new BookHasActiveRentalsException;
        }

        $book->delete();
    }
}
