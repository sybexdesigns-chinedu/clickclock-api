<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = array_values(array_filter(explode(', ', $this->file_url), fn ($file) => str_contains($file, '/videos/')))[0] ?? '';
        $thumbnail = str_replace('posts/videos', 'thumbnails', $url);
        $thumbnail = str_replace(['.mp4', '.mov', '.avi', '.wmv', '.mkv', '.webm', '.flv'], '.png', $thumbnail);
        return [
            'id' => $this->id,
            'posted_by' => [
                'id' => $this->user->id,
                'username' => $this->user->profile ? $this->user->profile->username : null,
                'image' => $this->user->profile ? asset('storage/'.$this->user->profile->image) : null,
            ],
            'caption' => $this->caption,
            'hashtags' => $this->hashtags,
            'file_url' => asset('storage/'.$url),
            'thumbnail' => asset('storage/'.$thumbnail),
            'likes_count' => $this->likes->count(),
            'is_liked' => $this->likes->contains('user_id', $request->user()->id),
            'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->exists() || $request->user()->id === $this->user->id,
            'comments_count' => formatNumber($this->comments->count()),
            'created_at' => $this->created_at,
        ];
    }
}
