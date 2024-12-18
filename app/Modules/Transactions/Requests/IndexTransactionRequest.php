<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Requests;

use App\Modules\Transactions\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class IndexTransactionRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'amount_min' => ['nullable', 'numeric'],
            'amount_max' => ['nullable', 'numeric', 'gte:amount_min'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', new Enum(TransactionType::class)],
            'sort.field' => ['nullable', 'string', 'in:amount,created_at,title'],
            'sort.direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    /**
     * Get the filters that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return array_filter($this->validated(), fn ($value) => !is_null($value));
    }
}
