<?php

namespace App\Modules\Transactions\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Currency\Facades\Currency;

class TransactionSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = is_array($this->resource) ? $this->resource : [];

        return [
            'data' => [
                'total_income' => $resource['total_income'] ?? 0,
                'total_expense' => $resource['total_expense'] ?? 0,
                'transaction_count' => $resource['transaction_count'] ?? 0,
            ]
        ];
    }
}
