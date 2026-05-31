<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rentals;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookRentalResource;
use App\Models\BookRental;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Rentals', weight: 4)]
class RentalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BookRental::class);

        /** @var User $user */
        $user = $request->user();

        $rentals = $user->rentals()
            ->with('book')
            ->latest('rented_at')
            ->paginate();

        return BookRentalResource::collection($rentals)->response();
    }

    public function show(BookRental $rental): JsonResponse
    {
        $this->authorize('view', $rental);

        return BookRentalResource::make($rental->load('book'))->response();
    }
}
