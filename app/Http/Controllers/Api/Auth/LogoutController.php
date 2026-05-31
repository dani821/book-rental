<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LogoutUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

#[Group('Auth', weight: 1)]
class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutUserAction $logoutUserAction): Response
    {
        /** @var User $user */
        $user = $request->user();

        $logoutUserAction->handle($user);

        return response()->noContent();
    }
}
