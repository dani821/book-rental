<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rentals;

use App\Actions\Rentals\UpdateReadingProgressAction;
use App\Exceptions\Rentals\RentalAlreadyFinishedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rentals\UpdateReadingProgressRequest;
use App\Http\Resources\BookRentalResource;
use App\Models\BookRental;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Rentals', weight: 4)]
class UpdateReadingProgressController extends Controller
{
    /**
     * @throws RentalAlreadyFinishedException
     */
    public function __invoke(UpdateReadingProgressRequest $request, BookRental $rental, UpdateReadingProgressAction $updateReadingProgressAction): JsonResponse
    {
        $this->authorize('update', $rental);

        /** @var array{current_page: int} $validated */
        $validated = $request->validated();

        $rental = $updateReadingProgressAction->handle(rental: $rental, currentPage: $validated['current_page']);

        return BookRentalResource::make($rental->load('book'))->response();
    }
}
