<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthLogoutResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'message' => 'Successfully logged out'
        ];
    }
}
