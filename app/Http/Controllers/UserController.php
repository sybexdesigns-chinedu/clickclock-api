<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

    public function show()
    {
        //
    }

    public function follow(Request $request, string $id)
    {
        $request->user()->following()->attach($id);
        return response()->json(['message' => 'You are now following this user.'], 200);
    }

    public function unfollow(Request $request, string $id)
    {
        $request->user()->following()->detach($id);
        return response()->json(['message' => 'You have unfollowed this user.'], 200);
    }

    public function getFollowers(Request $request, User $user)
    {
        return $user->followers->map(fn ($user) => [
            'id' => $user->id,
            'username' => $user->profile->username,
            'image' => asset('storage/' . $user->profile->image),
            'followed_at' => $user->pivot->created_at->diffForHumans(),
        ]);
    }

    public function getFollowing(Request $request, User $user)
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
