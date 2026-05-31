<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Exceptions\Rentals\RentalAlreadyFinishedException;
use App\Models\BookRental;

class UpdateReadingProgressAction
{
    /**
     * @throws RentalAlreadyFinishedException
     */
    public function handle(BookRental $rental, int $currentPage): BookRental
    {
        if ($rental->status->isCompleted()) {
            throw new RentalAlreadyFinishedException;
        }

        $rental->current_page = $currentPage;
        $rental->save();

        return $rental;
    }
}
