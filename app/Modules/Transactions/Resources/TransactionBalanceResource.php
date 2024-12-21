<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'balance' => [
                'EUR' => $this->resource['EUR'],
                'USD' => $this->resource['USD'],
            ],
        ];
    }
}
