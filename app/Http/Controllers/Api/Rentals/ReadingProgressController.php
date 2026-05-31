<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rentals;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookRentalResource;
use App\Models\BookRental;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Rentals', weight: 4)]
class ReadingProgressController extends Controller
{
    public function __invoke(BookRental $rental): JsonResponse
    {
        $this->authorize('view', $rental);

        return BookRentalResource::make($rental->load('book'))->response();
    }
}
