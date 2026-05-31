<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Rentals\RentBookAction;
use App\Enums\BookGenre;
use App\Enums\UserRole;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Throwable;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * @throws Throwable
     */
    public function run(RentBookAction $rentBookAction): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => UserRole::Admin,
        ]);

        $member = User::query()->create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
        ]);

        $books = collect($this->books())->map(
            fn (array $attributes): Book => Book::query()->create($attributes),
        );

        foreach ($books->take(3) as $book) {
            $rentBookAction->handle(user: $member, book: $book);
        }
    }

    /**
     * @return array<int, array{title: string, author: string, genre: BookGenre, isbn: string, published_year: int, total_pages: int, total_copies: int, available_copies: int}>
     */
    private function books(): array
    {
        return collect([
            ['To Kill a Mockingbird', 'Harper Lee', BookGenre::Fiction, '9780061120084', 1960, 281, 4],
            ['1984', 'George Orwell', BookGenre::Fiction, '9780451524935', 1949, 328, 5],
            ['A Brief History of Time', 'Stephen Hawking', BookGenre::Science, '9780553380163', 1988, 256, 3],
            ['Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', BookGenre::NonFiction, '9780062316097', 2011, 443, 4],
            ['The Hobbit', 'J. R. R. Tolkien', BookGenre::Fantasy, '9780547928227', 1937, 310, 5],
            ['The Lord of the Rings', 'J. R. R. Tolkien', BookGenre::Fantasy, '9780544003415', 1954, 1178, 2],
            ['Steve Jobs', 'Walter Isaacson', BookGenre::Biography, '9781451648539', 2011, 656, 3],
            ['The Diary of a Young Girl', 'Anne Frank', BookGenre::Biography, '9780553577129', 1947, 283, 4],
            ['Guns, Germs, and Steel', 'Jared Diamond', BookGenre::History, '9780393317558', 1997, 480, 3],
            ['The Selfish Gene', 'Richard Dawkins', BookGenre::Science, '9780192860927', 1976, 360, 2],
            ['Pride and Prejudice', 'Jane Austen', BookGenre::Fiction, '9780141439518', 1813, 432, 5],
            ['The Great Gatsby', 'F. Scott Fitzgerald', BookGenre::Fiction, '9780743273565', 1925, 180, 4],
            ['Cosmos', 'Carl Sagan', BookGenre::Science, '9780345539434', 1980, 365, 3],
            ['The Wright Brothers', 'David McCullough', BookGenre::Biography, '9781476728742', 2015, 320, 2],
            ["A People's History of the United States", 'Howard Zinn', BookGenre::History, '9780062397348', 1980, 729, 2],
            ["Harry Potter and the Philosopher's Stone", 'J. K. Rowling', BookGenre::Fantasy, '9780747532699', 1997, 223, 5],
            ['On the Origin of Species', 'Charles Darwin', BookGenre::Science, '9781509827695', 1859, 502, 1],
            ['Thinking, Fast and Slow', 'Daniel Kahneman', BookGenre::NonFiction, '9780374533557', 2011, 499, 3],
            ['The Name of the Wind', 'Patrick Rothfuss', BookGenre::Fantasy, '9780756404741', 2007, 662, 4],
            ['SPQR: A History of Ancient Rome', 'Mary Beard', BookGenre::History, '9781631492228', 2015, 606, 2],
        ])->map(fn (array $book): array => [
            'title' => $book[0],
            'author' => $book[1],
            'genre' => $book[2],
            'isbn' => $book[3],
            'published_year' => $book[4],
            'total_pages' => $book[5],
            'total_copies' => $book[6],
            'available_copies' => $book[6],
        ])->all();
    }
}
