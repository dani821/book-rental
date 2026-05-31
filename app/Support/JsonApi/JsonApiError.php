<?php

declare(strict_types=1);

namespace App\Support\JsonApi;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class JsonApiError implements Arrayable
{
    public function __construct(
        public string $status,
        public string $title,
        public ?string $detail = null,
        public ?string $pointer = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'title' => $this->title,
            'detail' => $this->detail,
            'source' => $this->pointer !== null ? ['pointer' => $this->pointer] : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
