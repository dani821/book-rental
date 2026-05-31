<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rentals;

use App\Actions\Rentals\RentBookAction;
use App\Exceptions\Rentals\BookNotAvailableException;
use App\Exceptions\Rentals\RentalAlreadyActiveException;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookRentalResource;
use App\Models\Book;
use App\Models\BookRental;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

#[Group('Rentals', weight: 4)]
class RentBookController extends Controller
{
    /**
     * @throws BookNotAvailableException
     * @throws RentalAlreadyActiveException
     * @throws Throwable
     */
    public function __invoke(Request $request, Book $book, RentBookAction $rentBookAction): JsonResponse
    {
        $this->authorize('create', BookRental::class);

        /** @var User $user */
        $user = $request->user();

        $rental = $rentBookAction->handle(user: $user, book: $book);

        return BookRentalResource::make($rental->load('book'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
