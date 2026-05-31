<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/users/{$user->id}/password", [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.0.source.pointer', '/current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_change_requires_confirmation(): void
    {
        $user = User::factory()->admin()->create();

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/users/{$user->id}/password", [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'mismatch',
        ])->assertStatus(422)
            ->assertJsonPath('errors.0.source.pointer', '/password');
    }

    public function test_member_cannot_change_another_users_password(): void
    {
        Sanctum::actingAs(User::factory()->member()->create());
        $other = User::factory()->create();

        $this->putJson("/api/v1/users/{$other->id}/password", [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(403);

        $this->assertTrue(Hash::check('password', $other->fresh()->password));
    }

    public function test_admin_can_change_another_users_password(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $other = User::factory()->create();

        $this->putJson("/api/v1/users/{$other->id}/password", [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertNoContent();

        $this->assertTrue(Hash::check('new-password', $other->fresh()->password));
    }

    public function test_member_can_change_their_own_password(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/users/{$user->id}/password", [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertNoContent();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_member_changing_own_password_with_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->member()->create();
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/users/{$user->id}/password", [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.0.source.pointer', '/current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_self_password_change_keeps_the_current_token_and_revokes_the_rest(): void
    {
        $user = User::factory()->member()->create();
        $staleToken = $user->createToken('stale')->plainTextToken;
        $currentToken = $user->createToken('current')->plainTextToken;

        $this->withToken($currentToken)->putJson("/api/v1/users/{$user->id}/password", [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertNoContent();

        $this->app['auth']->forgetGuards();
        $this->withToken($staleToken)->getJson('/api/v1/me')->assertStatus(401);

        $this->app['auth']->forgetGuards();
        $this->withToken($currentToken)->getJson('/api/v1/me')->assertOk();

        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_admin_reset_revokes_all_of_the_targets_tokens(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->member()->create();
        $tokenA = $target->createToken('a')->plainTextToken;
        $tokenB = $target->createToken('b')->plainTextToken;
        $adminToken = $admin->createToken('admin')->plainTextToken;

        $this->withToken($adminToken)->putJson("/api/v1/users/{$target->id}/password", [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertNoContent();

        $this->app['auth']->forgetGuards();
        $this->withToken($tokenA)->getJson('/api/v1/me')->assertStatus(401);

        $this->app['auth']->forgetGuards();
        $this->withToken($tokenB)->getJson('/api/v1/me')->assertStatus(401);

        $this->assertSame(0, $target->tokens()->count());

        $this->app['auth']->forgetGuards();
        $this->withToken($adminToken)->getJson('/api/v1/me')->assertOk();
    }
}
