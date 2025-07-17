<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reward;

class UserController extends Controller
{
    public function getTopBroadcasters(Request $request)
    {
        $users = User::whereHas('profile')
            ->withCount('followers as followers_count')
            ->having('followers_count', '>', 3)
            ->get()
            ->sortByDesc(fn ($user) => $user->profile->country == 'United Kingdom' ? 1 : 0);
        return $users->map(fn($user) => [
            'id' => $user->id,
            'username' => $user->profile->username,
            'image' => asset('storage/' . $user->profile->image),
            'followers' => $user->followers_count,
            'country' => $user->profile->country
        ]);
    }

    public function getLeaderboard()
    {
        $rewards = Reward::orderByDesc('weekly_coins_spent')
            ->take(50)
            ->get();
        return $rewards->map(fn($reward) => [
            'id' => $reward->user->id,
            'username' => $reward->user->profile->username,
            'name' => $reward->user->profile->name,
            'image' => asset('storage/' . $reward->user->profile->image),
            'coins_spent' => formatNumber($reward->weekly_coins_spent)
        ]);
    }

    public function show()
    {
        //
    }

    public function follow(Request $request, string $id)
    {
        $request->user()->following()->sync($id, false); // Use sync with false to avoid detaching other followers
        return response()->json(['message' => 'You are now following this user.'], 200);
    }

    public function unfollow(Request $request, string $id)
    {
        $request->user()->following()->detach($id);
        return response()->json(['message' => 'You have unfollowed this user.'], 200);
    }

    public function getFollowers(User $user)
    {
        return $user->followers->map(fn ($follower) => [
            'id' => $follower->id,
            'username' => $follower->profile->username,
            'image' => asset('storage/' . $follower->profile->image),
            'followed_at' => $follower->pivot->created_at->diffForHumans(),
            'is_following' => $user->following()->where('following_id', $follower->id)->exists()
        ]);
    }

    public function getFollowing(User $user)
    {
        return $user->following->map(fn ($user) => [
            'id' => $user->id,
            'username' => $user->profile->username,
            'image' => asset('storage/' . $user->profile->image),
            'followed_at' => $user->pivot->created_at->diffForHumans(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->noContent();
    }
}
