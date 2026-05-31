<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_returns_a_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.type', 'users')
            ->assertJsonPath('data.attributes.email', $user->email);

        $this->assertIsString($response->json('meta.token'));
        $this->assertNotEmpty($response->json('meta.token'));
    }

    public function test_login_with_invalid_credentials_returns_a_json_api_error(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonStructure([
                'errors' => [
                    ['status', 'title', 'detail', 'source' => ['pointer']],
                ],
            ])
            ->assertJsonPath('errors.0.status', '422')
            ->assertJsonPath('errors.0.detail', 'The provided credentials are incorrect.')
            ->assertJsonPath('errors.0.source.pointer', '/email');
    }

    public function test_login_is_throttled_and_returns_a_json_api_429_with_retry_after(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $ignored) {
            $this->postJson('/api/v1/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429)
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertHeader('Retry-After')
            ->assertJsonPath('errors.0.status', '429');
    }
}
