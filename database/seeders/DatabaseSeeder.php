<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Rentals\RentBookAction;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @throws \Throwable
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $member = User::factory()->member()->createOne([
            'name' => 'Member User',
            'email' => 'member@example.com',
        ]);

        $books = Book::factory()->count(20)->create();

        $rentBookAction = app(RentBookAction::class);

        foreach ($books->take(3) as $book) {
            $rentBookAction->handle(user: $member, book: $book);
        }
    }
}
