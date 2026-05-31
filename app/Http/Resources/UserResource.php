<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    /**
     * @var array<int, string>
     */
    public array $attributes = [
        'name',
        'email',
        'role',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
