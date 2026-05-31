<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\RentalStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookSearchQuery
{
    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = QueryBuilder::for(Book::class)
            ->allowedFilters(
                AllowedFilter::partial('title'),
                AllowedFilter::partial('author'),
                AllowedFilter::exact('genre'),
                AllowedFilter::scope('available'),
            )
            ->allowedSorts('title', 'author', 'published_year', 'created_at')
            ->defaultSort('title');

        if (! $user->isAdmin()) {
            $query->withExists(['rentals as is_rented_by_current_user' => fn (Builder $rentalQuery) => $rentalQuery
                ->where('user_id', $user->id)
                ->where('status', RentalStatus::Active->value)]);
        }

        /** @var LengthAwarePaginator<int, Book> $paginator */
        $paginator = $query->paginate($perPage);

        return $paginator->withQueryString();
    }
}
