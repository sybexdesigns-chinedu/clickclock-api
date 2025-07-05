<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return new ProfileResource($request->user()->profile);
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

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->noContent();
    }
}
