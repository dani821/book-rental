<?php

declare(strict_types=1);

namespace App\Actions\Books;

use App\Models\Book;

class CreateBookAction
{
    /**
     * @param  array{title: string, author: string, genre: string, isbn?: string|null, published_year?: int|null, total_pages: int, total_copies?: int}  $attributes
     */
    public function handle(array $attributes): Book
    {
        $totalCopies = (int) ($attributes['total_copies'] ?? 1);

        return Book::query()->create([
            ...$attributes,
            'total_copies' => $totalCopies,
            'available_copies' => $totalCopies,
        ]);
    }
}
