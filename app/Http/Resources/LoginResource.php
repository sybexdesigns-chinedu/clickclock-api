<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'user' => new UserResource($this),
            'profile' => $this->profile()->exists() ? [
                'id' => $this->profile->id,
                'username' => $this->profile->username,
                'name' => $this->profile->name,
                'dob' => $this->profile->dob,
                'gender' => $this->profile->gender,
                'phone' => $this->profile->phone,
                'bio' => $this->profile->bio,
                'image' => asset('storage/'.$this->profile->image),
                'city' => $this->profile->city,
                'country' => $this->profile->country,
                'interests' => $this->interests->pluck('name'),
            ] : null,
            'token' => $this->token,
            // 'stream_token' => $this->stream_token,
        ];
    }
}
