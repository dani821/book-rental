<?php

declare(strict_types=1);

namespace Tests\Feature\Rentals;

use App\Enums\RentalStatus;
use App\Models\Book;
use App\Models\BookRental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RentalTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_rent_an_available_book_and_available_copies_decrement(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);

        $this->postJson("/api/v1/books/{$book->id}/rentals")
            ->assertCreated()
            ->assertJsonPath('data.type', 'rentals')
            ->assertJsonPath('data.attributes.status', RentalStatus::Active->value)
            ->assertJsonPath('data.attributes.current_page', 0);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 0]);
        $this->assertDatabaseHas('book_rentals', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => RentalStatus::Active->value,
        ]);
    }

    public function test_renting_a_book_with_no_available_copies_returns_conflict(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $book = Book::factory()->unavailable()->create();

        $this->postJson("/api/v1/books/{$book->id}/rentals")
            ->assertStatus(409)
            ->assertJsonPath('errors.0.title', 'Book Not Available');
    }

    public function test_renting_the_same_book_twice_returns_conflict(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_copies' => 5, 'available_copies' => 5]);

        $this->postJson("/api/v1/books/{$book->id}/rentals")->assertCreated();

        $this->postJson("/api/v1/books/{$book->id}/rentals")
            ->assertStatus(409)
            ->assertJsonPath('errors.0.title', 'Rental Already Active');
    }

    public function test_single_copy_cannot_be_oversold(): void
    {
        $book = Book::factory()->create(['total_copies' => 1, 'available_copies' => 1]);

        Sanctum::actingAs(User::factory()->member()->create());
        $this->postJson("/api/v1/books/{$book->id}/rentals")->assertCreated();

        Sanctum::actingAs(User::factory()->member()->create());
        $this->postJson("/api/v1/books/{$book->id}/rentals")
            ->assertStatus(409)
            ->assertJsonPath('errors.0.title', 'Book Not Available');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 0]);
    }

    public function test_extending_a_rental_increments_count_and_pushes_due_date(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $rental = BookRental::factory()->active()->create(['user_id' => $user->id]);
        $originalDue = $rental->due_at;

        $this->patchJson("/api/v1/rentals/{$rental->id}/extend")
            ->assertOk()
            ->assertJsonPath('data.attributes.extensions_count', 1);

        $rental->refresh();
        $this->assertSame(1, $rental->extensions_count);
        $this->assertTrue($rental->due_at->gt($originalDue));
    }

    public function test_extending_beyond_the_limit_returns_unprocessable(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'extensions_count' => 2,
        ]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/extend")
            ->assertStatus(422)
            ->assertJsonPath('errors.0.title', 'Extension Limit Reached');
    }

    public function test_acting_on_a_completed_rental_returns_conflict(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $rental = BookRental::factory()->completed()->create(['user_id' => $user->id]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/extend")
            ->assertStatus(409)
            ->assertJsonPath('errors.0.title', 'Rental Already Finished');

        $this->patchJson("/api/v1/rentals/{$rental->id}/progress", ['current_page' => 5])
            ->assertStatus(409);

        $this->patchJson("/api/v1/rentals/{$rental->id}/finish")
            ->assertStatus(409);
    }

    public function test_updating_progress_beyond_total_pages_is_rejected(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_pages' => 100]);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/progress", ['current_page' => 101])
            ->assertStatus(422);
    }

    public function test_updating_progress_reflects_in_progress_percentage(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_pages' => 200]);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/progress", ['current_page' => 50])
            ->assertOk()
            ->assertJsonPath('data.attributes.current_page', 50)
            ->assertJsonPath('data.attributes.progress_percentage', 25);
    }

    public function test_finishing_a_rental_completes_it_and_restores_a_copy(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 1]);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/finish")
            ->assertOk()
            ->assertJsonPath('data.attributes.status', RentalStatus::Completed->value);

        $rental->refresh();
        $this->assertSame(RentalStatus::Completed, $rental->status);
        $this->assertNotNull($rental->returned_at);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 2]);
    }

    public function test_finishing_a_rental_does_not_push_available_copies_above_total(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 2]);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/finish")
            ->assertOk()
            ->assertJsonPath('data.attributes.status', RentalStatus::Completed->value);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 2]);
    }

    public function test_finishing_an_already_finished_rental_returns_conflict_and_restores_a_copy_only_once(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_copies' => 2, 'available_copies' => 1]);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/finish")->assertOk();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 2]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/finish")
            ->assertStatus(409)
            ->assertJsonPath('errors.0.title', 'Rental Already Finished');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'available_copies' => 2]);
    }

    public function test_a_user_cannot_view_another_users_rental(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $othersRental = BookRental::factory()->active()->create();

        $this->getJson("/api/v1/rentals/{$othersRental->id}")->assertStatus(403);
    }

    public function test_a_user_cannot_act_on_another_users_rental(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $othersRental = BookRental::factory()->active()->create();

        $this->patchJson("/api/v1/rentals/{$othersRental->id}/extend")->assertStatus(403);
        $this->patchJson("/api/v1/rentals/{$othersRental->id}/finish")->assertStatus(403);
    }

    public function test_index_only_returns_the_current_users_rentals(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        BookRental::factory()->active()->count(2)->create(['user_id' => $user->id]);
        BookRental::factory()->active()->count(3)->create();

        $this->getJson('/api/v1/rentals')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'rentals');
    }

    public function test_reading_progress_endpoint_returns_the_rental(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->create(['total_pages' => 100]);
        $rental = BookRental::factory()->active()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'current_page' => 40,
        ]);

        $this->getJson("/api/v1/rentals/{$rental->id}/progress")
            ->assertOk()
            ->assertJsonPath('data.attributes.progress_percentage', 40);
    }

    public function test_renting_endpoints_require_authentication(): void
    {
        $book = Book::factory()->create();

        $this->postJson("/api/v1/books/{$book->id}/rentals")->assertStatus(401);
    }

    public function test_a_rental_cannot_be_extended_beyond_the_maximum_across_repeated_calls(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);
        $rental = BookRental::factory()->active()->create(['user_id' => $user->id]);

        $this->patchJson("/api/v1/rentals/{$rental->id}/extend")
            ->assertOk()
            ->assertJsonPath('data.attributes.extensions_count', 1);

        $this->patchJson("/api/v1/rentals/{$rental->id}/extend")
            ->assertOk()
            ->assertJsonPath('data.attributes.extensions_count', 2);

        $this->patchJson("/api/v1/rentals/{$rental->id}/extend")
            ->assertStatus(422)
            ->assertJsonPath('errors.0.title', 'Extension Limit Reached');
    }
}
