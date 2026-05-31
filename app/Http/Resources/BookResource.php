<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class BookResource extends JsonApiResource
{
    /**
     * @var array<int, string>
     */
    public array $attributes = [
        'title',
        'author',
        'genre',
        'isbn',
        'published_year',
        'total_pages',
        'total_copies',
        'available_copies',
        'created_at',
        'updated_at',
    ];
}
