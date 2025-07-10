<?php

namespace App\Http\Controllers;

use App\Models\CommentReply;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\ReplyResource;

class CommentReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'comment_id' => 'required|integer|exists:comments,id',
            'body' => 'required|string'
        ]);
        $data['user_id'] = $request->user()->id;
        $reply = new CommentReply($data);
        if(!screenInput($data['body'])) $reply->status = 'pending';
        else {
            $reply->comment->post->increment('no_of_engagements');
            if($reply->comment->user_id !== $reply->user_id) {
                Notification::create([
                    'user_id' => $reply->comment->user_id,
                    'action' => 'comment reply',
                    'type' => 'comment',
                    'action_id' => $reply->comment->post->id,
                    'message' => "{$request->user()->profile->username} replied to your comment",
                ]);
            }
            if($reply->comment->post->user_id !== $reply->user_id) {
                Notification::create([
                    'user_id' => $reply->comment->post->user_id,
                    'action' => 'post comment',
                    'type' => 'comment',
                    'action_id' => $reply->comment->post->id,
                    'message' => "{$request->user()->profile->username} commented on your post",
                ]);
            }
        }
        $reply->save();
        return response()->json(['message' => 'Comment created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return ReplyResource::collection(CommentReply::where('comment_id', $id)->where('status', 'approved')->orderByDesc('created_at')->get());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommentReply $reply)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommentReply $reply)
    {
        if($request->user()->id !== $reply->user_id)
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        $data = $request->validate([
            'body' => 'required|string'
        ]);
        if(!screenInput($data['body'])) {
            $reply->comment->post->decrement('no_of_engagements');
            $data['status'] = 'pending';
        }
        $reply->update($data);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommentReply $reply)
    {
        if(request()->user()->id !== $reply->user_id)
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        $reply->comment->post->decrement('no_of_engagements');
        $reply->delete();
        return response('', 204);
    }
}
