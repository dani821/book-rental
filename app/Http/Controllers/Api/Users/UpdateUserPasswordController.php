<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\UpdateUserPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateUserPasswordRequest;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

#[Group('Users', weight: 2)]
class UpdateUserPasswordController extends Controller
{
    public function __invoke(UpdateUserPasswordRequest $request, User $user, UpdateUserPasswordAction $updateUserPasswordAction): Response
    {
        $this->authorize('updatePassword', $user);

        /** @var array{password: string} $validated */
        $validated = $request->validated();

        /** @var User $actingUser */
        $actingUser = $request->user();

        $updateUserPasswordAction->handle(
            user: $user,
            newPassword: $validated['password'],
            keepTokenId: $actingUser->is($user) ? $this->currentTokenId($request) : null,
        );

        return response()->noContent();
    }

    private function currentTokenId(Request $request): ?int
    {
        $bearer = $request->bearerToken();

        if ($bearer === null) {
            return null;
        }

        $key = PersonalAccessToken::findToken($bearer)?->getKey();

        return is_int($key) ? $key : null;
    }
}
