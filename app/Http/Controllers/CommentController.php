<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
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
            'post_id' => 'required|integer',
            'body' => 'required|string'
        ]);
        $data['user_id'] = $request->user()->id;
        $comment = new Comment($data);
        if(!$comment->post->allow_comments) return response()->json(['message' => 'Comments are not allowed on this post'], 403);
        if(!screenInput($data['body'])) $comment->status = 'pending';
        else {
            $comment->post->most_engagements_points++;
            $comment->post->no_of_engagements++;
            if($comment->post->user_id !== $comment->user_id) {
                Notification::create([
                    'user_id' => $comment->post->user_id,
                    'action' => 'post comment',
                    'action_id' => $comment->post->id,
                    'message' => "{$request->user()->profile->username} commented on your post",
                ]);
            }
        }
        $comment->push();
        return response()->json(['message' => 'Comment created successfully'], 201);
    }

    /**
     * Display the specified resource.
     * $id is the post id
     */
    public function show(string $id)
    {
        return CommentResource::collection(Comment::where('post_id', $id)->where('status', 'approved')->orderByDesc('created_at')->get());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        if(request()->user()->id !== $comment->user_id)
            return response()->json(['message' => 'You are not authorized to update this comment'], 403);
        $data = $request->validate([
            'body' => 'required|string'
        ]);
        if(!screenInput($data['body'])) {
            $comment->post->most_engagements_points++;
            $comment->post->no_of_engagements--;
            $comment->push();
            $data['status'] = 'pending';
        }
        $comment->update($data);
        return response()->json(['message' => 'Comment updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if(request()->user()->id !== $comment->user_id)
            return response()->json(['message' => 'You are not authorized to delete this comment'], 403);
        $replyCount = $comment->replies->count();
        $comment->post->no_of_engagements -= $replyCount;
        $comment->post->most_engagements_points -= $replyCount + 1;
        $comment->post->save();
        $comment->delete();
        return response('', 204);
    }
}
