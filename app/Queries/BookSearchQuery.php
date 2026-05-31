<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookSearchQuery
{
    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Book> $paginator */
        $paginator = QueryBuilder::for(Book::class)
            ->allowedFilters(
                AllowedFilter::partial('title'),
                AllowedFilter::partial('author'),
                AllowedFilter::exact('genre'),
                AllowedFilter::scope('available'),
            )
            ->allowedSorts('title', 'author', 'published_year', 'created_at')
            ->defaultSort('title')
            ->paginate($perPage);

        return $paginator->withQueryString();
    }
}
