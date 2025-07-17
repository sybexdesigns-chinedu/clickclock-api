<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use App\Models\livestream;
use Illuminate\Http\Request;

class LivestreamController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);
        if ($request->user()->followers()->count() < 500) {
            return response()->json(['message' => 'You need at least 500 followers to start a livestream'], 403);
        }
        livestream::create([
            'user_id' => $request->user()->id,
            'moderator_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'country' => getCountryFromIp(),
            'type' => 'single'
        ]);
        return response()->json(['message' => 'Livestream created successfully'], 201);
    }

    public function show(livestream $livestream)
    {
        return response()->json($livestream, 200);
    }

    public function sendGift(Request $request, livestream $livestream)
    {
        $request->validate([
            'gift_id' => 'required|exists:gifts,id'
        ]);
        $gift = Gift::find($request->gift_id);
        if ($request->user()->coins < $gift->price) {
            return response()->json(['message' => 'You do not have enough coins'], 403);
        }
        // if ($livestream->user_id == $request->user()->id) {
        //     return response()->json(['message' => 'You cannot send a gift to yourself'], 403);
        // }
        $request->user()->coins -= $gift->price;
        $request->user()->reward->coins_spent += $gift->price;
        $request->user()->reward->weekly_coins_spent += $gift->price;
        $request->user()->push();
        // $livestream->coins_earned += ($gift->price * 70)/100;//remove 30% for the platform
        $livestream->coins_earned += $gift->price;//remove 30% for the platform
        $livestream->save();
        $livestream->user->reward->diamonds += ($gift->price * 70)/100;
        $livestream->user->reward->coins_earned += ($gift->price * 70)/100;
        $livestream->gifters()->create([
            'gifter_id' => $request->user()->id,
            'creator_id' => $livestream->user_id,
            'gift_id' => $gift->id,
            'amount' => $gift->price
        ]);
        $livestream->comments()->create([
            'user_id' => $request->user()->id,
            'body' => "Sent a $gift->name ".asset('storage/gifts/'.$gift->icon)
        ]);
        return response()->json(['message' => 'Gift sent successfully'], 200);
    }

    public function like(Livestream $livestream)
    {
        $livestream->increment('no_of_likes', 100);
        return response()->json(['message' => 'Livestream liked successfully'], 200);
    }

    public function comment(Request $request, livestream $livestream)
    {
        $request->validate([
            'body' => ['required', 'string', 'max:255',
                function($attribute, $value, $fail) {
                    if (!screenInput($value)) {
                        return $fail("The comment failed our content moderation test.");
                    }
                }
            ]
        ]);
        $livestream->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body
        ]);
        return response()->json(['message' => 'Comment added successfully'], 201);
    }

    public function getComments(livestream $livestream)
    {
        return response()->json($livestream->comments, 200);
    }

    public function viewerJoins(Request $request, livestream $livestream)
    {
        if (in_array($request->user()->id, explode(',', $livestream->block_list))) {
            return response()->json(['message' => 'You have been blocked from this livestream'], 403);
        }
        if ($livestream->viewers()->where('user_id', $request->user()->id)->exists()) {
            $livestream->viewers()->where('user_id', $request->user()->id)->first()->update(['is_watching' => true]);
        }
        else {
            $livestream->viewers()->create([
                'user_id' => $request->user()->id
            ]);
        }
        $livestream->comments()->create([
            'user_id' => $request->user()->id,
            'body' => 'joined the livestream'
        ]);
        return response()->json(['message' => 'You have joined the livestream'], 201);
    }

    public function viewerLeaves(Request $request, livestream $livestream)
    {
        $livestream->viewers()->where('user_id', $request->user()->id)->first()->update(['is_watching' => false]);
        $livestream->comments()->create([
            'user_id' => $request->user()->id,
            'body' => 'Left the livestream'
        ]);
        return response()->json(['message' => 'You have left the livestream'], 201);
    }

    public function removeViewer(Request $request, livestream $livestream)
    {
        $request->validate([
            'viewer_id' => 'required|exists:users,id'
        ]);
        $livestream->viewers()->where('user_id', $request->viewer_id)->firstOrFail()->update(['is_watching' => false]);
        $livestream->comments()->create([
            'user_id' => $request->viewer_id,
            'body' => 'Removed from the livestream'
        ]);
        //add user to blocked list
        $livestream->block_list == null ? $livestream->block_list = $request->viewer_id : $livestream->block_list .= "{,$request->viewer_id}";
        return response()->json(['message' => 'Viewer removed successfully'], 201);
    }

    public function changeModerator(Request $request, livestream $livestream)
    {
        $request->validate([
            'moderator_id' => 'required|exists:users,id'
        ]);
        if ($livestream->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You are not authorized to change the moderator'], 403);
        }
        $livestream->moderator_id = $request->moderator_id;
        $livestream->save();
        return response()->json(['message' => 'Moderator changed successfully'], 200);
    }

    public function sendRequest(Request $request, livestream $livestream)
    {
        $livestream->requests()->create([
            'user_id' => $request->user()->id
        ]);
        return response()->json(['message' => 'You have sent a request to join the livestream'], 201);
    }

    public function acceptRequest(Request $request, livestream $livestream)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        if ($livestream->user_id !== $request->user()->id || $livestream->moderator_id !== $request->user()->id) {
            return response()->json(['message' => 'You are not authorized to accept the request'], 403);
        }
        $livestream->requests()->where('user_id', $request->user())->firstOrFail()->delete();
        $livestream->guests()->create([
            'user_id' => $request->user_id
        ]);
        $livestream->comments()->create([
            'user_id' => $request->user()->id,
            'body' => 'Joined the livestream as a guest'
        ]);
        $new_livestream = $livestream->replicate();
        $new_livestream->user_id = $request->user_id;
        $new_livestream->moderator_id = $request->user_id;
        $new_livestream->type = 'multi';
        $new_livestream->parent_livestream_id = $livestream->id;
        $new_livestream->coins_earned = 0;
        $new_livestream->is_creator = false;
        $new_livestream->save();
        $livestream->type = 'multi';
        $livestream->save();
        return response()->json(['message' => 'Request accepted successfully'], 200);
    }

    public function endLivestream(livestream $livestream)
    {
        $livestream->viewers()->update(['is_watching' => false]);
        $livestream->children()->update(['is_active' => false]);
        $livestream->no_of_views = $livestream->viewers()->count();
        $livestream->is_active = false;
        $livestream->user->reward->increment('no_of_livestreams');
        // $livestream->user->reward->diamonds += ($livestream->coins_earned * 70)/100;
        // $livestream->user->reward->coins_earned += ($livestream->coins_earned * 70)/100;
        // $livestream->push();
        $livestream->save();
        return response()->json(['message' => 'Livestream ended successfully'], 200);
    }
}
