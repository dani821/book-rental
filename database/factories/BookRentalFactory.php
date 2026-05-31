<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RentalStatus;
use App\Models\Book;
use App\Models\BookRental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookRental>
 */
class BookRentalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => RentalStatus::Active,
            'current_page' => 0,
            'extensions_count' => 0,
            'rented_at' => now(),
            'due_at' => now()->addDays(BookRental::DEFAULT_RENTAL_DAYS),
            'returned_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RentalStatus::Active,
            'due_at' => now()->addDays(BookRental::DEFAULT_RENTAL_DAYS),
            'returned_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => RentalStatus::Completed,
            'returned_at' => now(),
        ]);
    }
}
