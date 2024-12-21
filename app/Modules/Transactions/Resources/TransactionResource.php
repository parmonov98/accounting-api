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
        if (!is_object($this->resource)) {
            return [];
        }

        return [
            'id' => $this->resource->id,
            'author_id' => $this->resource->author_id,
            'title' => $this->resource->title,
            'amount' => $this->resource->amount,
            'type' => $this->resource->type,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
