<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;

class UpdateBookAction
{
    /**
     * When the total number of copies changes, the available count is shifted by the
     * same delta so the number of copies currently rented out is preserved, clamped to
     * the range [0, total_copies].
     *
     * @param  array{title?: string, author?: string, genre?: string, isbn?: string|null, published_year?: int|null, total_pages?: int, total_copies?: int}  $attributes
     */
    public function handle(Book $book, array $attributes): Book
    {
        if (array_key_exists('total_copies', $attributes)) {
            $rentedOut = $book->total_copies - $book->available_copies;
            $attributes['available_copies'] = max(0, $attributes['total_copies'] - $rentedOut);
        }

        $book->update($attributes);

        return $book;
    }
}
