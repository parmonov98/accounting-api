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
        return [
            'total_income' => $this->resource['total_income'],
            'total_expense' => $this->resource['total_expense'],
            'transaction_count' => $this->resource['transaction_count'],
        ];
    }
}
