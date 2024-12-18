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
        $totalIncome = $this->resource['total_income'] ?? 0;
        $totalExpense = $this->resource['total_expense'] ?? 0;
        $total = $totalIncome - $totalExpense;

        return [
            'total_income' => [
                'EUR' => $totalIncome,
                'USD' => Currency::driver()->getRate('EUR', 'USD') * $totalIncome,
            ],
            'total_expense' => [
                'EUR' => $totalExpense,
                'USD' => Currency::driver()->getRate('EUR', 'USD') * $totalExpense,
            ],
            'total' => [
                'EUR' => $total,
                'USD' => Currency::driver()->getRate('EUR', 'USD') * $total,
            ],
            'period' => [
                'start' => $this->resource['period']['start'] ?? null,
                'end' => $this->resource['period']['end'] ?? null,
            ],
        ];
    }
}
