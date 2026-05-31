<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookGenre;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalCopies = fake()->numberBetween(1, 5);

        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'genre' => fake()->randomElement(BookGenre::cases()),
            'isbn' => fake()->unique()->isbn13(),
            'published_year' => fake()->numberBetween(1950, now()->year),
            'total_pages' => fake()->numberBetween(50, 1200),
            'total_copies' => $totalCopies,
            'available_copies' => $totalCopies,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'available_copies' => 0,
        ]);
    }
}
