<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Books\BookController;
use App\Http\Controllers\Api\Rentals\ExtendRentalController;
use App\Http\Controllers\Api\Rentals\FinishRentalController;
use App\Http\Controllers\Api\Rentals\ReadingProgressController;
use App\Http\Controllers\Api\Rentals\RentalController;
use App\Http\Controllers\Api\Rentals\RentBookController;
use App\Http\Controllers\Api\Rentals\UpdateReadingProgressController;
use App\Http\Controllers\Api\Users\MeController;
use App\Http\Controllers\Api\Users\UpdateUserPasswordController;
use App\Http\Controllers\Api\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('register', RegisterController::class)->middleware('throttle:register');
    Route::post('login', LoginController::class)->middleware('throttle:login');
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('me', MeController::class);
        Route::put('users/{user}/password', UpdateUserPasswordController::class);
        Route::apiResource('users', UserController::class)->except(['update']);

        Route::apiResource('books', BookController::class);
        Route::apiResource('rentals', RentalController::class)->only(['index', 'show']);
        Route::post('books/{book}/rentals', RentBookController::class);
        Route::patch('rentals/{rental}/extend', ExtendRentalController::class);
        Route::get('rentals/{rental}/progress', ReadingProgressController::class);
        Route::patch('rentals/{rental}/progress', UpdateReadingProgressController::class);
        Route::patch('rentals/{rental}/finish', FinishRentalController::class);
    });
});
