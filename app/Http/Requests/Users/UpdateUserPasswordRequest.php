<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPasswordRequest extends FormRequest
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
            'current_password' => [
                Rule::when(
                    $this->isUpdatingOwnPassword(),
                    ['required', 'current_password:sanctum'],
                    ['nullable'],
                ),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    private function isUpdatingOwnPassword(): bool
    {
        $authenticated = $this->user();
        $target = $this->route('user');

        return $authenticated instanceof User
            && $target instanceof User
            && $authenticated->is($target);
    }
}
