<?php

declare(strict_types=1);

namespace App\Http\Requests\Books;

use App\Enums\BookGenre;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'genre' => ['required', Rule::enum(BookGenre::class)],
            'isbn' => ['nullable', 'string', 'max:20', 'unique:books,isbn'],
            'published_year' => ['nullable', 'integer', 'min:1', 'max:'.now()->year],
            'total_pages' => ['required', 'integer', 'min:1'],
            'total_copies' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
