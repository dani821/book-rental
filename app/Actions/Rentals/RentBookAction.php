<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Enums\RentalStatus;
use App\Exceptions\Rentals\BookNotAvailableException;
use App\Exceptions\Rentals\RentalAlreadyActiveException;
use App\Models\Book;
use App\Models\BookRental;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class RentBookAction
{
    /**
     * @throws RentalAlreadyActiveException
     * @throws BookNotAvailableException
     * @throws Throwable
     */
    public function handle(User $user, Book $book): BookRental
    {
        return DB::transaction(function () use ($user, $book): BookRental {
            $lockedBook = Book::query()
                ->select(['id', 'available_copies', 'total_copies'])
                ->whereKey($book->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $hasActiveRental = $user->rentals()
                ->where('book_id', $lockedBook->id)
                ->where('status', RentalStatus::Active)
                ->exists();

            if ($hasActiveRental) {
                throw new RentalAlreadyActiveException;
            }

            if ($lockedBook->available_copies < 1) {
                throw new BookNotAvailableException;
            }

            $lockedBook->decrement('available_copies');

            return BookRental::query()
                ->create([
                    'user_id' => $user->id,
                    'book_id' => $lockedBook->id,
                    'status' => RentalStatus::Active,
                    'current_page' => 0,
                    'extensions_count' => 0,
                    'rented_at' => now(),
                    'due_at' => now()->addDays(BookRental::DEFAULT_RENTAL_DAYS),
                ]);
        });
    }
}
