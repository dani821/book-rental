<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RentalStatus;
use Database\Factories\BookRentalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property RentalStatus $status
 * @property int $current_page
 * @property int $extensions_count
 * @property Carbon $rented_at
 * @property Carbon $due_at
 * @property Carbon|null $returned_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $progress_percentage
 * @property-read Book $book
 * @property-read User $user
 */
#[Fillable(['user_id', 'book_id', 'status', 'current_page', 'extensions_count', 'rented_at', 'due_at', 'returned_at'])]
class BookRental extends Model
{
    /** @use HasFactory<BookRentalFactory> */
    use HasFactory;

    public const int DEFAULT_RENTAL_DAYS = 14;

    public const int EXTENSION_DAYS = 14;

    public const int MAX_EXTENSIONS = 2;

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The reading progress expressed as a whole percentage of the book's pages.
     *
     * @return Attribute<int, never>
     */
    protected function progressPercentage(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->book->total_pages > 0
                ? (int) round($this->current_page / $this->book->total_pages * 100)
                : 0,
        );
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => RentalStatus::class,
            'current_page' => 'integer',
            'extensions_count' => 'integer',
            'rented_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }
}
