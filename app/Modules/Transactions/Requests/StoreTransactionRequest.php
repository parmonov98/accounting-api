<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTransactionRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'integer'],
        ];
    }

    /**
     * Get the validated data with author_id added.
     *
     * @return array<string, mixed>
     */
    public function validatedWithUser(): array
    {
        return array_merge(
            $this->validated(),
            ['author_id' => $this->user()->id]
        );
    }
}
