<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_a_member_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.type', 'users')
            ->assertJsonPath('data.attributes.name', 'Jane Doe')
            ->assertJsonPath('data.attributes.email', 'jane@example.com')
            ->assertJsonPath('data.attributes.role', 'member')
            ->assertJsonMissingPath('data.attributes.password');

        $this->assertIsString($response->json('meta.token'));
        $this->assertNotEmpty($response->json('meta.token'));

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'member',
        ]);
    }

    public function test_register_ignores_a_provided_role(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Mallory',
            'email' => 'mallory@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.role', 'member');

        $this->assertDatabaseHas('users', [
            'email' => 'mallory@example.com',
            'role' => 'member',
        ]);
    }

    public function test_register_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('errors.0.status', '422')
            ->assertJsonPath('errors.0.source.pointer', '/email');
    }
}
