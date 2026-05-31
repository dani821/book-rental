<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Enums\RentalStatus;
use App\Exceptions\Rentals\RentalAlreadyFinishedException;
use App\Models\Book;
use App\Models\BookRental;
use Illuminate\Support\Facades\DB;
use Throwable;

class FinishRentalAction
{
    /**
     * @throws Throwable
     * @throws RentalAlreadyFinishedException
     */
    public function handle(BookRental $rental): BookRental
    {
        return DB::transaction(function () use ($rental): BookRental {
            $lockedRental = BookRental::query()
                ->whereKey($rental->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRental->status->isCompleted()) {
                throw new RentalAlreadyFinishedException;
            }

            $lockedRental->status = RentalStatus::Completed;
            $lockedRental->returned_at = now();
            $lockedRental->save();

            $lockedBook = Book::query()
                ->whereKey($lockedRental->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBook->available_copies < $lockedBook->total_copies) {
                $lockedBook->increment('available_copies');
            }

            return $lockedRental;
        });
    }
}
