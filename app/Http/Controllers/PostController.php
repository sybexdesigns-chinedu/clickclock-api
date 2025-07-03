<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use App\Http\Resources\ReelResource;
use App\Jobs\GenerateVideoThumbnail;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $current_location = getCountryFromIp();
        // $following = request()->user()->following->pluck('id');
        // $query = Post::query();
        // $query->where(function ($q) use ($following) {
        //     $q->where('status', 'approved'); //post is approved
        //     $q->whereIn('user_id', $following); //post belongs to a user you are following
        //     $q->whereNot('privacy', 'private'); //post is not private
        // });
        // $query->orWhere(function ($q) use ($current_location) {
        //     $q->where('status', 'approved'); //post is approved
        //     $q->where('meta_location', $current_location); //post originated from your current location
        //     $q->where('privacy', 'public'); //post is set to public
        //     $q->where('user_id', '!=', request()->user()->id); //post does not belong to you
        // });
        // $posts = $query->orderByDesc('id')->get();
        $posts = Post::orderByDesc('id')->get();
        return PostResource::collection($posts);
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
            'privacy' => 'required|string|in:public,friends,private',
            'allow_comments' => 'required|boolean',
            'allow_duets' => 'required|boolean',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'location' => 'nullable|string',
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv'
        ]);
        $path = [];
        if($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if(str_starts_with($file->getMimeType(), 'image/')) {
                    $fileName = microtime(true).'.'.$file->extension();
                    $file_loc = $file->storeAs('posts/images', $fileName, 'public');
                    $path[] = $file_loc;
                    $moderationResult = moderateImage($file_loc);
                    if(!$moderationResult['status']) {
                        $data['status'] = 'pending';
                        $data['remark'] = $moderationResult['reason'];
                    }
                }
                else {
                    $fileName = microtime(true);
                    $fileNameWithExtension = $fileName.'.'.$file->extension();
                    $path[] = $file->storeAs('posts/videos', $fileNameWithExtension, 'public');
                    $data['has_video'] = true;
                    // GenerateVideoThumbnail::dispatch($fileNameWithExtension, $fileName)->onQueue('thumbnails');
                }
            }
        }
        unset($data['files']);
        if(!empty($data['body']) && !screenInput($data['body'])) $data['status'] = 'pending';
        $data['file_url'] = implode(', ', $path);
        $data['hashtags'] = !empty($data['hashtags']) ? implode(', ', $data['hashtags']) : null;
        $data['user_id'] = $request->user()->id;
        $data['meta_location'] = "get location from frontend";
        $post = new Post($data);
        $post->save();
        return response()->json(['message' => 'Post created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostResource($post, true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        if($request->user()->id !== $post->user_id) abort(403);
        // return $request->all();
        $data = $request->validate([
            'privacy' => 'required|string|in:public,friends,private',
            'allow_comments' => 'required|boolean',
            'allow_duets' => 'required|boolean',
            'caption' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'location' => 'nullable|string',
            'files.*' => 'file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv'
        ]);
        if($post->file_url){
            $files = explode(', ', $post->file_url);
            foreach ($files as $file) {
                unlink(public_path('storage/'.$file));
            }
        }
        $path = [];
        if($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if(str_starts_with($file->getMimeType(), 'image/')) {
                    $fileName = microtime(true).'.'.$file->extension();
                    $file_loc = $file->storeAs('posts/images', $fileName, 'public');
                    $path[] = $file_loc;
                    $moderationResult = moderateImage($file_loc);
                    if(!$moderationResult['status']) {
                        $data['status'] = 'pending';
                        $data['remark'] = $moderationResult['reason'];
                    }
                }
                else {
                    $fileName = microtime(true);
                    $fileNameWithExtension = $fileName.'.'.$file->extension();
                    $path[] = $file->storeAs('posts/videos', $fileNameWithExtension, 'public');
                    $data['has_video'] = true;
                    // GenerateVideoThumbnail::dispatch($fileNameWithExtension, $fileName)->onQueue('thumbnails');
                }
            }
        }
        unset($data['files']);
        if($data['body'] && !screenInput($data['body'])) $data['status'] = 'pending';
        $data['file_url'] = implode(', ', $path);
        $data['hashtags'] = implode(', ', $data['hashtags']);
        $post->update($data);
        return response()->json(['message' => 'Post updated successfully']);;
    }

    /**
     * Like the specified post.
     */
    public function like(Request $request, Post $post)
    {
        $like = $post->likes()->firstOrCreate(['user_id' => request()->user()->id, 'is_active' => true]);
        if (!$like->is_active) {
            $like->is_active = true;
            $like->save();
        }
        else $post->most_engagements_points++;
        $post->no_of_engagements++;
        $post->save();
        if($post->user_id !== request()->user()->id) {
            Notification::create([
                'user_id' => $post->user_id,
                'action' => 'post like',
                'action_id' => $post->id,
                'message' => "{$request->user()->profile->username} liked your post",
            ]);
        }
        return response()->json(['message' => 'Post liked successfully'], 201);
    }

    /**
     * Like the specified post.
     */
    public function unlike(Post $post)
    {
        $status = $post->likes()->where('user_id', request()->user()->id)->update(['is_active' => false]);
        if($status > 0 && $post->no_of_engagements > 0) $post->no_of_engagements--;
        $post->save();
        return response()->json(['message' => 'Post unliked successfully'], 201);;
    }

    public function getTopHashtags()
    {
        return Post::getTopHashtags(10, 7);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if($post->file_url){
            $files = explode(', ', $post->file_url);
            foreach ($files as $file) {
                unlink(public_path('storage/'.$file));
            }
        }
        $post->delete();
        return response()->noContent();
    }
}
