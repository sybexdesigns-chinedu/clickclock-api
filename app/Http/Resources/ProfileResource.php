<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    protected bool $showExtra;

    public function __construct($resource, $showExtra = true)
    {
        parent::__construct($resource);
        $this->showExtra = $showExtra;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $posts = collect();
        if ($this->showExtra) {
            $posts = $request->user()->posts()->withCount('likes')->get();
        }
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'image' => asset('storage/'.$this->image),
            'city' => $this->city,
            'country' => $this->country,
            'social_link' => $this->social_link,
            // 'interests' => $this->user->interests->pluck('name'),
            $this->mergeWhen($this->showExtra, [
                'posts_count' => formatNumber($this->user->posts->count()),
                'likes_count' => formatNumber($posts->sum('likes_count')),
                'followers_count' => formatNumber($this->user->followers()->count()),
                'following_count' => formatNumber($this->user->following()->count()),
                'is_following' => $this->user->followers()->where('follower_id', $request->user()->id)->exists() || $request->user()->id === $this->user->id,
                'posts' => PostResource::collection($posts)
            ]),
        ];
    }
}
