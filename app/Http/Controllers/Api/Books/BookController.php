<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Books;

use App\Actions\Books\CreateBookAction;
use App\Actions\Books\DeleteBookAction;
use App\Actions\Books\UpdateBookAction;
use App\Enums\RentalStatus;
use App\Exceptions\Books\BookHasActiveRentalsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Books\StoreBookRequest;
use App\Http\Requests\Books\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Models\User;
use App\Queries\BookSearchQuery;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

#[Group('Books', weight: 3)]
class BookController extends Controller
{
    #[QueryParameter('filter[title]', description: 'Filter by a partial, case-insensitive title match.', type: 'string', example: 'dune')]
    #[QueryParameter('filter[author]', description: 'Filter by a partial, case-insensitive author match.', type: 'string', example: 'herbert')]
    #[QueryParameter('filter[genre]', description: 'Filter by exact genre.', type: 'string', example: 'fantasy')]
    #[QueryParameter('filter[available]', description: 'When truthy, only books with at least one available copy are returned.', type: 'boolean', example: true)]
    #[QueryParameter('sort', description: 'Sort field: one of title, author, published_year, created_at. Prefix with "-" for descending (e.g. -published_year).', type: 'string', default: 'title', example: '-published_year')]
    #[QueryParameter('page', description: 'Page number of the paginated result set (15 books per page).', type: 'integer', default: 1, example: 1)]
    public function index(Request $request, BookSearchQuery $bookSearchQuery): JsonResponse
    {
        $this->authorize('viewAny', Book::class);

        /** @var User $user */
        $user = $request->user();

        return BookResource::collection($bookSearchQuery->paginate($user))->response();
    }

    public function store(StoreBookRequest $request, CreateBookAction $createBookAction): JsonResponse
    {
        $this->authorize('create', Book::class);

        /** @var array{title: string, author: string, genre: string, isbn?: string|null, published_year?: int|null, total_pages: int, total_copies?: int} $validated */
        $validated = $request->validated();

        $book = $createBookAction->handle($validated);

        return BookResource::make($book)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, Book $book): JsonResponse
    {
        $this->authorize('view', $book);

        /** @var User $user */
        $user = $request->user();

        // Only members need the "already rented by me" flag; skip the sub-select for admins.
        if (! $user->isAdmin()) {
            $book->loadExists(['rentals as is_rented_by_current_user' => fn (Builder $query) => $query
                ->where('user_id', $user->id)
                ->where('status', RentalStatus::Active->value)]);
        }

        return BookResource::make($book)->response();
    }

    public function update(UpdateBookRequest $request, Book $book, UpdateBookAction $updateBookAction): JsonResponse
    {
        $this->authorize('update', $book);

        /** @var array{title?: string, author?: string, genre?: string, isbn?: string|null, published_year?: int|null, total_pages?: int, total_copies?: int} $validated */
        $validated = $request->validated();

        $book = $updateBookAction->handle(book: $book, attributes: $validated);

        return BookResource::make($book)->response();
    }

    /**
     * @throws BookHasActiveRentalsException
     */
    public function destroy(Book $book, DeleteBookAction $deleteBookAction): Response
    {
        $this->authorize('delete', $book);

        $deleteBookAction->handle($book);

        return response()->noContent();
    }
}
