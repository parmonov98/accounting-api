<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthLoginResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user' => UserResource::make($this->resource['user']),
            'token' => $this->resource['token']
        ];
    }
}
