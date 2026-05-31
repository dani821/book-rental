<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/{path?}', 'app')
    ->where('path', '^(?!api|docs|sanctum|build|storage|up).*$');
