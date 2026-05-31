<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rentals;

use App\Actions\Rentals\ExtendRentalAction;
use App\Exceptions\Rentals\ExtensionLimitReachedException;
use App\Exceptions\Rentals\RentalAlreadyFinishedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookRentalResource;
use App\Models\BookRental;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Rentals', weight: 4)]
class ExtendRentalController extends Controller
{
    /**
     * @throws ExtensionLimitReachedException
     * @throws RentalAlreadyFinishedException
     */
    public function __invoke(BookRental $rental, ExtendRentalAction $extendRentalAction): JsonResponse
    {
        $this->authorize('update', $rental);

        $rental = $extendRentalAction->handle($rental);

        return BookRentalResource::make($rental->load('book'))->response();
    }
}
