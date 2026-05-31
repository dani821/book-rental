<?php

declare(strict_types=1);

namespace App\Http\Requests\Books;

use App\Enums\BookGenre;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
        /** @var Book|null $book */
        $book = $this->route('book');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'author' => ['sometimes', 'string', 'max:255'],
            'genre' => ['sometimes', Rule::enum(BookGenre::class)],
            'isbn' => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book?->id)],
            'published_year' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:'.now()->year],
            'total_pages' => ['sometimes', 'integer', 'min:1'],
            'total_copies' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
