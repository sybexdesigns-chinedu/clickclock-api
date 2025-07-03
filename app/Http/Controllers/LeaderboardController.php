<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;

class LeaderboardController extends Controller
{
    public function getLeaderboard()
    {
        $most_popular = User::whereHas('profile')->orderBy('most_popular_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'image' => asset('storage/'.$user->profile->image),
                'firstname' =>$user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'points' => $user->most_popular_points,
                'followers_count' => formatNumber($user->followers()->where('is_active', true)->count())
            ]);

        $most_followed = User::whereHas('profile')->orderBy('most_followed_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'image' => asset('storage/'.$user->profile->image),
                'firstname' =>$user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'points' => $user->most_followed_points,
                'followers_count' => formatNumber($user->followers()->where('is_active', true)->count())
            ]);

        $most_matches = User::whereHas('profile')->orderBy('most_matches_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'image' => asset('storage/'.$user->profile->image),
                'firstname' =>$user->profile->firstname,
                'lastname' => $user->profile->lastname,
                'points' => $user->most_matches_points,
                'followers_count' => formatNumber($user->followers()->where('is_active', true)->count())
            ]);

        $most_engagements = Post::whereRelation('user', 'id', '!=', 0)->orderBy('most_engagements_points', 'desc')
            ->take(50)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->user->id,
                'post_id' => $post->id,
                'image' => asset('storage/'.$post->user->profile->image),
                'firstname' =>$post->user->profile->firstname ?? '',
                'lastname' => $post->user->profile->lastname ?? '',
                'points' => $post->most_engagements_points,
                'followers_count' => formatNumber($post->user->followers()->where('is_active', true)->count())
            ]);

        return response()->json([
            'most_popular' => $most_popular,
            'most_followed' => $most_followed,
            'most_matches' => $most_matches,
            'most_engaged' => $most_engagements
        ]);
    }

    // public function getMostFollowed()
    // {
    // }

    // public function getMostMatches()
    // {
    // }

    // public function getMostEngaged()
    // {
    // }
}
