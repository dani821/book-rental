<?php

declare(strict_types=1);

namespace App\Actions\Rentals;

use App\Exceptions\Rentals\ExtensionLimitReachedException;
use App\Exceptions\Rentals\RentalAlreadyFinishedException;
use App\Models\BookRental;

class ExtendRentalAction
{
    /**
     * @throws ExtensionLimitReachedException
     * @throws RentalAlreadyFinishedException
     */
    public function handle(BookRental $rental): BookRental
    {
        if ($rental->status->isCompleted()) {
            throw new RentalAlreadyFinishedException;
        }

        if ($rental->extensions_count >= BookRental::MAX_EXTENSIONS) {
            throw new ExtensionLimitReachedException;
        }

        $rental->extensions_count++;
        $rental->due_at = $rental->due_at->addDays(BookRental::EXTENSION_DAYS);
        $rental->save();

        return $rental;
    }
}
