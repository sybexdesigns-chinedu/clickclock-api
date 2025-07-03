<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
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
            'body' => $this->body,
            'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user->id,
                'username' => $this->user->profile->username,
                'image' => asset('storage/'.$this->user->profile->image)
            ]
        ];
    }
}
