<?php

declare(strict_types=1);

namespace App\Modules\Transactions\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{


    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        if (!$this->resource instanceof \App\Modules\Transactions\Models\Transaction) {
            return [];
        }

        return [
            'data' => [
                'id' => $this->id,
                'author_id' => $this->author_id,
                'title' => $this->title,
                'amount' => $this->amount,
                'type' => $this->type->value,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }

}
