<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rentals;

use App\Actions\Rentals\FinishRentalAction;
use App\Exceptions\Rentals\RentalAlreadyFinishedException;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookRentalResource;
use App\Models\BookRental;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Rentals', weight: 4)]
class FinishRentalController extends Controller
{
    /**
     * @throws \Throwable
     * @throws RentalAlreadyFinishedException
     */
    public function __invoke(BookRental $rental, FinishRentalAction $finishRentalAction): JsonResponse
    {
        $this->authorize('update', $rental);

        $rental = $finishRentalAction->handle($rental);

        return BookRentalResource::make($rental->load('book'))->response();
    }
}
