<?php

namespace App\Http\Controllers;

use App\Http\Resources\StatusResource;
use App\Http\Resources\StatusViewersResource;
use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = request()->user()->following()->with(['statuses' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->whereHas('statuses') // Only users who have posted a status
        ->withMax('statuses', 'created_at') // Add virtual column: latest status timestamp
        ->orderByDesc('statuses_max_created_at') // Sort users by latest status
        ->get();
        $users = $users->sortBy(fn ($u) => $u['id'] === request()->user()->id ? 0 : 1)->values();
        return StatusResource::collection($users);
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
        // $request->validate([
        //     'status' => 'required|array',
        //     'status.*.color' => 'nullable|string|max:191',
        //     'status.*.content' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv', 'max:5120',
        //         function ($attribute, $value, $fail) use ($request) {
        //             $file = $request->file($attribute);
        //             if(str_starts_with($file->getMimeType(), 'image/')) {
        //                 $moderation_result = moderateImage($file->getRealPath(), true);
        //                 if($moderation_result['status'] === false) {
        //                     $fail("The value in $attribute failed our content moderation test due to {$moderation_result['reason']}.");
        //                 }
        //             }
        //         }
        //     ],
        //     'status.*.caption' => ['nullable', 'string', 'max:191',
        //         function ($attribute, $value, $fail) {
        //             if (!screenInput($value)) {
        //                 $fail("The value in $attribute failed our content moderation test.");
        //             }
        //         }
        //     ],
        // ]);
        $request->validate([
            'color' => 'nullable|string|max:191',
            'content' => ['nullable', 'required_without:caption', 'file', 'mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,wmv,mkv,webm,flv', 'max:5120',
                function ($attribute, $value, $fail) use ($request) {
                    $file = $request->file($attribute);
                    if(str_starts_with($file->getMimeType(), 'image/')) {
                        $moderation_result = moderateImage($file->getRealPath(), true);
                        if($moderation_result['status'] === false) {
                            $fail("The value in $attribute failed our content moderation test due to {$moderation_result['reason']}.");
                        }
                    }
                }
            ],
            'caption' => ['nullable', 'required_without:content', 'string', 'max:191',
                function ($attribute, $value, $fail) {
                    if (!screenInput($value)) {
                        $fail("The value in $attribute failed our content moderation test.");
                    }
                }
            ],
        ], [
            'caption.required_without' => 'Either text, image or video is required.',
            'content.required_without' => 'Either text, image or video is required.',
        ]);
        if ($request->hasFile("content")) {
            $file = $request->file("content");
            if(str_starts_with($file->getMimeType(), 'image/')) {
                $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                $content = $file->storeAs('statuses/images', $fileName, 'public');
                $type = 'image';
            }
            else {
                $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
                $content = $file->storeAs('statuses/videos', $fileName, 'public');
                $type = 'video';
            }
        } else $type = 'text';
        Status::create([
            'user_id' => request()->user()->id,
            'content' => $content ?? null,
            'caption' => $request->caption ?? null,
            'type' => $type,
            'color' => $request->color ?? null,
        ]);
        // $content = $type = null;
        // foreach($request->status as $key => $value) {
        //     if ($request->hasFile("status.$key.content")) {
        //         $file = $request->file("status.$key.content");
        //         if(str_starts_with($file->getMimeType(), 'image/')) {
        //             $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
        //             $content = $file->storeAs('statuses/images', $fileName, 'public');
        //             $type = 'image';
        //         }
        //         else {
        //             $fileName = microtime(true).'.'.$file->getClientOriginalExtension();
        //             $content = $file->storeAs('statuses/videos', $fileName, 'public');
        //             $type = 'video';
        //         }
        //     } else $type = 'text';
        //     Status::create([
        //         'user_id' => request()->user()->id,
        //         'content' => $content ?? null,
        //         'caption' => $value['caption'] ?? null,
        //         'type' => $type,
        //         'color' => $value['color'] ?? null,
        //     ]);
        //     $content = $type = null;
        // }

        return response()->json(['message' => 'Story added successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Status $status)
    {
        if ($status->user_id !== request()->user()->id) {
            $status->viewed_by()->sync(request()->user()->id, false);
        }
        return response()->noContent();
    }

    public function getViewers(Status $status)
    {
        $viewers = $status->viewed_by;
        return StatusViewersResource::collection($viewers);

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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status)
    {
        if ($status->user_id !== request()->user()->id) {
            return response()->json(['message' => 'You are not authorized to delete this story'], 403);
        }
        if ($status->type !== 'text') {
            $path = public_path('storage/'.$status->content);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $status->delete();
        return response()->json(['message' => 'Story deleted successfully'], 200);
    }
}
