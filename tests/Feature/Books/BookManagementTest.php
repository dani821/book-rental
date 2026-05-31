<?php

declare(strict_types=1);

namespace Tests\Feature\Books;

use App\Enums\BookGenre;
use App\Models\Book;
use App\Models\BookRental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_index_is_paginated_and_json_api_shaped(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        Book::factory()->count(3)->create();

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'type', 'attributes' => ['title', 'author', 'genre', 'total_pages', 'total_copies', 'available_copies']],
                ],
                'links',
                'meta',
            ])
            ->assertJsonPath('data.0.type', 'books');
    }

    public function test_books_can_be_filtered_by_author(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        Book::factory()->create(['author' => 'Ursula K. Le Guin']);
        Book::factory()->create(['author' => 'Isaac Asimov']);

        $this->getJson('/api/v1/books?filter[author]=Guin')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.author', 'Ursula K. Le Guin');
    }

    public function test_books_can_be_filtered_by_genre(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        Book::factory()->create(['genre' => BookGenre::Science]);
        Book::factory()->create(['genre' => BookGenre::Fantasy]);

        $this->getJson('/api/v1/books?filter[genre]='.BookGenre::Science->value)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.genre', BookGenre::Science->value);
    }

    public function test_books_can_be_sorted_by_title(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        Book::factory()->create(['title' => 'Zebra']);
        Book::factory()->create(['title' => 'Apple']);

        $this->getJson('/api/v1/books?sort=title')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Apple')
            ->assertJsonPath('data.1.attributes.title', 'Zebra');
    }

    public function test_available_filter_hides_books_with_no_copies(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        Book::factory()->create(['title' => 'In Stock']);
        Book::factory()->unavailable()->create(['title' => 'Sold Out']);

        $this->getJson('/api/v1/books?filter[available]=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.title', 'In Stock');
    }

    public function test_available_filter_set_to_false_shows_only_books_with_no_copies(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        Book::factory()->create(['title' => 'In Stock']);
        Book::factory()->unavailable()->create(['title' => 'Sold Out']);

        $this->getJson('/api/v1/books?filter[available]=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.title', 'Sold Out');
    }

    public function test_member_can_view_a_book(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $book = Book::factory()->create();

        $this->getJson("/api/v1/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.id', (string) $book->id)
            ->assertJsonPath('data.type', 'books');
    }

    public function test_member_cannot_create_a_book(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());

        $this->postJson('/api/v1/books', [
            'title' => 'New Book',
            'author' => 'Someone',
            'genre' => BookGenre::Fiction->value,
            'total_pages' => 100,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('books', ['title' => 'New Book']);
    }

    public function test_member_cannot_update_a_book(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $book = Book::factory()->create();

        $this->patchJson("/api/v1/books/{$book->id}", ['title' => 'Changed'])
            ->assertStatus(403);
    }

    public function test_member_cannot_delete_a_book(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $book = Book::factory()->create();

        $this->deleteJson("/api/v1/books/{$book->id}")->assertStatus(403);
        $this->assertModelExists($book);
    }

    public function test_admin_can_create_a_book_with_available_copies_derived(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/v1/books', [
            'title' => 'Dune',
            'author' => 'Frank Herbert',
            'genre' => BookGenre::Science->value,
            'total_pages' => 412,
            'total_copies' => 4,
        ])->assertCreated()
            ->assertJsonPath('data.type', 'books')
            ->assertJsonPath('data.attributes.available_copies', 4);

        $this->assertDatabaseHas('books', [
            'title' => 'Dune',
            'total_copies' => 4,
            'available_copies' => 4,
        ]);
    }

    public function test_admin_can_update_a_book(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $book = Book::factory()->create(['title' => 'Old']);

        $this->patchJson("/api/v1/books/{$book->id}", ['title' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.attributes.title', 'New');
    }

    public function test_admin_can_delete_a_book_without_rentals(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $book = Book::factory()->create();

        $this->deleteJson("/api/v1/books/{$book->id}")->assertNoContent();
        $this->assertSoftDeleted($book);
    }

    public function test_deleting_a_book_with_active_rentals_returns_conflict(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $book = Book::factory()->create();
        BookRental::factory()->active()->create(['book_id' => $book->id]);

        $this->deleteJson("/api/v1/books/{$book->id}")
            ->assertStatus(409)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure(['errors' => [['status', 'title', 'detail']]])
            ->assertJsonPath('errors.0.status', '409')
            ->assertJsonPath('errors.0.title', 'Book Has Active Rentals');

        $this->assertModelExists($book);
    }

    public function test_books_index_flags_books_the_current_user_has_an_active_rental_for(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);

        $rented = Book::factory()->create(['title' => 'Rented']);
        Book::factory()->create(['title' => 'Available']);
        BookRental::factory()->active()->create(['user_id' => $user->id, 'book_id' => $rented->id]);

        $this->getJson('/api/v1/books?sort=title')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.title', 'Available')
            ->assertJsonPath('data.0.attributes.is_rented_by_current_user', false)
            ->assertJsonPath('data.1.attributes.title', 'Rented')
            ->assertJsonPath('data.1.attributes.is_rented_by_current_user', true);
    }

    public function test_rented_flag_is_scoped_to_the_current_user(): void
    {
        $user = User::factory()->member()->create();
        $otherUser = User::factory()->member()->create();
        Sanctum::actingAs($user);

        $book = Book::factory()->create();
        BookRental::factory()->active()->create(['user_id' => $otherUser->id, 'book_id' => $book->id]);

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.is_rented_by_current_user', false);
    }

    public function test_a_completed_rental_does_not_flag_a_book_as_rented(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);

        $book = Book::factory()->create();
        BookRental::factory()->completed()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.is_rented_by_current_user', false);
    }

    public function test_book_show_flags_an_active_rental_for_the_current_user(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);

        $book = Book::factory()->create();
        BookRental::factory()->active()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $this->getJson("/api/v1/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.attributes.is_rented_by_current_user', true);
    }

    public function test_rented_flag_is_not_computed_for_admins(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $book = Book::factory()->create();
        // Even if an admin had a rental, the flag is never computed for admins.
        BookRental::factory()->active()->create(['user_id' => $admin->id, 'book_id' => $book->id]);

        $this->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.is_rented_by_current_user', false);

        $this->getJson("/api/v1/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.attributes.is_rented_by_current_user', false);
    }

    public function test_books_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/books')->assertStatus(401);
    }
}
