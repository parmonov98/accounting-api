<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Registration request validation.
 *
 * @bodyParam name string required User's full name. Example: John Doe
 * @bodyParam email string required User's email address. Must be unique. Example: john@example.com
 * @bodyParam password string required User's password. Must be at least 8 characters. Example: secret123
 * @bodyParam password_confirmation string required Password confirmation. Must match password. Example: secret123
 */
final class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        return parent::validated($key, $default);
    }
}
