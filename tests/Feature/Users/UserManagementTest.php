<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\BookRental;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_list_users(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());

        $this->getJson('/api/v1/users')
            ->assertStatus(403)
            ->assertJsonPath('errors.0.status', '403');
    }

    public function test_member_cannot_create_users(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());

        $this->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_member_cannot_view_another_user(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $other = User::factory()->create();

        $this->getJson("/api/v1/users/{$other->id}")->assertStatus(403);
    }

    public function test_member_cannot_delete_users(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $other = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$other->id}")->assertStatus(403);

        $this->assertModelExists($other);
    }

    public function test_admin_can_list_users_with_json_api_pagination_links(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        User::factory()->count(3)->create();

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'data' => [
                    ['id', 'type', 'attributes' => ['name', 'email', 'role', 'created_at']],
                ],
                'links',
            ])
            ->assertJsonPath('data.0.type', 'users');
    }

    public function test_admin_can_create_a_user_with_a_role(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson('/api/v1/users', [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ])->assertCreated()
            ->assertJsonPath('data.type', 'users')
            ->assertJsonPath('data.attributes.role', 'admin');

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_another_user(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $other = User::factory()->create();

        $this->getJson("/api/v1/users/{$other->id}")
            ->assertOk()
            ->assertJsonPath('data.id', (string) $other->id);
    }

    public function test_admin_can_delete_another_user(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $other = User::factory()->create();

        $this->deleteJson("/api/v1/users/{$other->id}")->assertNoContent();

        $this->assertModelMissing($other);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/users/{$admin->id}")->assertStatus(403);

        $this->assertModelExists($admin);
    }

    public function test_users_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/users')
            ->assertStatus(401)
            ->assertJsonPath('errors.0.status', '401');
    }

    public function test_viewing_a_missing_user_returns_a_json_api_404(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson('/api/v1/users/999999')
            ->assertStatus(404)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure(['errors' => [['status', 'title', 'detail']]])
            ->assertJsonPath('errors.0.status', '404');
    }

    public function test_deleting_a_user_with_an_active_rental_returns_conflict(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $member = User::factory()->member()->create();
        BookRental::factory()->active()->create(['user_id' => $member->id]);

        $this->deleteJson("/api/v1/users/{$member->id}")
            ->assertStatus(409)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure(['errors' => [['status', 'title', 'detail']]])
            ->assertJsonPath('errors.0.status', '409')
            ->assertJsonPath('errors.0.title', 'User Has Active Rentals');

        $this->assertModelExists($member);
    }

    public function test_deleting_a_user_with_only_completed_rentals_succeeds_and_cascades(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $member = User::factory()->member()->create();
        $rental = BookRental::factory()->completed()->create(['user_id' => $member->id]);

        $this->deleteJson("/api/v1/users/{$member->id}")->assertNoContent();

        $this->assertModelMissing($member);
        $this->assertModelMissing($rental);
    }
}
