<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'user' => new UserResource(request()->user()),
            'profile' => new ProfileResource(request()->user()->profile, true)
        ]);
    }

    /**
     * searching for records.
     */
    public function search()
    {
        //
    }

    public function checkUsername(Request $request)
    {
        $suggestions = [];
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/', function ($attribute, $value, $fail) use (&$suggestions) {
                if (Profile::where('username', $value)->exists()) {
                    // Generate unique username suggestions
                    $suggestions = $this->generateUsernameSuggestions($value);
                    // Return validation failure with suggestions
                    $fail('The username is already taken.');
                }
            }]
        ], [
            'username.regex' => 'Username can only contain letters, numbers and underscores. No spaces are allowed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Username is already taken',
                'errors' => $validator->errors(),
                'suggestions' => $suggestions
            ], 422);
        }

        return response()->json(['message' => 'Username is available']);
    }

    /**
     * Generate unique username suggestions based on availability.
     */
    private function generateUsernameSuggestions($username)
    {
        $suggestions = [];
        $baseUsernames = [
            $username . rand(100, 999),
            $username . '_' . rand(10, 99),
            rand(10, 99) . $username,
            $username . '_official',
            $username . '_real',
            $username . '_dev'
        ];

        foreach ($baseUsernames as $suggestedUsername) {
            if (!Profile::where('username', $suggestedUsername)->exists()) {
                $suggestions[] = $suggestedUsername;
            }

            // Stop once we have 3 unique suggestions
            if (count($suggestions) >= 3) {
                break;
            }
        }
        return $suggestions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->user()->profile()->exists()) {
            return new ProfileResource($request->user()->profile,false);
        }
        $data = $request->validate([
            'username' => 'required|string|regex:/^[a-zA-Z0-9_]+$/|min:3|max:20|unique:profiles,username',
            'name' => 'required|string|regex:/^[a-zA-ZÀ-ÖØ-öø-ÿ\' -]+$/|max:255',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'users' => 'nullable|array',
            'users.*' => 'integer',
            'interests' => 'required|array',
            'interests.*' => 'integer',
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120',
                function ($attribute, $value, $fail) use ($request) {
                    $moderation_result = moderateImage($request->file($attribute)->getRealPath(), true);
                    if(!$moderation_result['status']) {
                        $fail("Your profile image failed our content moderation policy due to {$moderation_result['reason']}");
                    }
                }
            ],
            'city' => 'required|string',
            'country' => 'required|string'
        ], [
            'username.regex' => 'Username can only contain letters, numbers or underscores. No spaces are allowed',
            'name.regex' => 'Name must contain only letters, hyphens, or apostrophes.',
        ]);

        $imageName = $request->username.'.'.$request->file('image')->extension();
        $path = $request->file('image')->storeAs('profile_images', $imageName, 'public');
        $data['image'] = $path;
        // $data['meta_location'] = getCountryFromIp();
        unset($data['interests']);
        unset($data['users']);
        $profile = new Profile($data);
        // return $request->users;
        DB::beginTransaction();
        try {
            $request->user()->profile()->save($profile);
            $profile->user->reward()->create();
            $request->user()->interests()->attach($request->interests);
            $request->users ? $request->user()->following()->attach($request->users) : null;

            DB::commit(); // If everything succeeds, commit the transaction
            return response()->json([
                'message' => 'Profile created successfully',
                'profile' => new ProfileResource($profile, false)
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // If anything fails, rollback all changes
            return $e;
            // return response()->json(['message' => 'An unexpected error occurred. Please try again'], 400); // Optionally, handle the error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $profile = Profile::where('user_id', $id)->first();
        return new ProfileResource($profile);
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
        $data = $request->validate([
            'bio' => 'nullable|string',
            'social_link' => 'nullable|url'
        ]);
        $request->user()->profile()->update($data);
        return response()->json(['message' => 'Profile updated successfully']);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 403);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return response()->json(['message' => 'Password changed successfully']);
    }

    public function changeImage(Request $request) {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120',
                function ($attribute, $value, $fail) use ($request) {
                    $moderation_result = moderateImage($request->file($attribute)->getRealPath(), true);
                    if(!$moderation_result['status']) {
                        $fail("Your profile image failed our content moderation policy due to {$moderation_result['reason']}");
                    }
                }
            ],
        ]);
        $user = $request->user();
        $imageName = $user->profile->image;
        if ($request->file('image')->store($imageName, 'public'))
            return response()->json(['message' => 'Profile image updated successfully']);
        else
            return response()->json(['message' => 'Failed to update profile image'], 500);
    }

    public function togglePrivacy(Request $request)
    {
        $profile = $request->user()->profile;
        $profile->is_private = !$profile->is_private;
        $profile->save();
        return response()->json(['message' => 'Profile privacy updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
