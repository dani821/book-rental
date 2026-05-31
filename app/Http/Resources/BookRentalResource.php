<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Override;

class BookRentalResource extends JsonApiResource
{
    /**
     * @var array<int, string>
     */
    public array $attributes = [
        'status',
        'current_page',
        'progress_percentage',
        'extensions_count',
        'rented_at',
        'due_at',
        'returned_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<int, string>
     */
    public array $relationships = [
        'book',
    ];

    #[Override]
    public function toType(Request $request): string
    {
        return 'rentals';
    }
}
