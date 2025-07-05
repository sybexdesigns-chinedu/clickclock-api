<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'coins' => $this->coins,
            // 'is_verified' => $this->is_verified,
            'status' => $this->status,
            'is_social' => $this->is_social
        ];
    }
}
