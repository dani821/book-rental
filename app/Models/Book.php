<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookGenre;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property string $title
 * @property string $author
 * @property BookGenre $genre
 * @property string|null $isbn
 * @property int|null $published_year
 * @property int $total_pages
 * @property int $total_copies
 * @property int $available_copies
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, BookRental> $rentals
 */
#[Fillable(['title', 'author', 'genre', 'isbn', 'published_year', 'total_pages', 'total_copies', 'available_copies'])]
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /**
     * @return HasMany<BookRental, $this>
     */
    public function rentals(): HasMany
    {
        return $this->hasMany(BookRental::class);
    }

    /**
     * Scope the query to books that have at least one available copy.
     *
     * @param  Builder<Book>  $query
     */
    public function scopeAvailable(Builder $query, bool|int|string $value = true): void
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('available_copies', '>', 0);
        } else {
            $query->where('available_copies', '=', 0);
        }
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'genre' => BookGenre::class,
            'published_year' => 'integer',
            'total_pages' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
        ];
    }
}
