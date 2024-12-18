<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SummaryTransactionRequest extends FormRequest
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
            'date_from' => ['required', 'date', 'before_or_equal:date_to'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * Get the validated date range from the request.
     *
     * @return array{date_from: string, date_to: string}
     */
    public function getDateRange(): array
    {
        return $this->only(['date_from', 'date_to']);
    }
}
