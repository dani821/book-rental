<?php

declare(strict_types=1);

namespace App\Http\Requests\Rentals;

use App\Models\BookRental;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var BookRental|null $rental */
        $rental = $this->route('rental');

        if ($rental !== null) {
            $rental->loadMissing('book');
        }

        return [
            'current_page' => ['required', 'integer', 'min:1', 'max:'.($rental?->book->total_pages ?? 1)],
        ];
    }
}
