<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    protected bool $single;

    public function __construct($resource, $single = false)
    {
        parent::__construct($resource);
        $this->single = $single;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'privacy' => $this->privacy,
            'allow_comments' => $this->allow_comments,
            'caption' => $this->caption,
            'hashtags' => $this->hashtags,
            'location' => $this->location,
            'file_url' => $this->file_url ? array_map(fn($file) => asset('storage/' . $file), explode(', ', $this->file_url))  : [],
            'created_at' => $this->created_at,
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile ? $this->user->profile->username : null,
                'image' => $this->user->profile ? asset('storage/'.$this->user->profile->image) : null,
            ],
            'likes' => $this->likes->pluck('user_id'),
            'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->where('is_active', true)->exists() || $this->user->id === $request->user()->id,
            'comments_count' => formatNumber($this->comments->count())
        ];
    }
}
