<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'allow_comments' => 'boolean',
        'allow_like_counts' => 'boolean',
        'has_video' => 'boolean'
    ];

    public static function getTopHashtags($limit, $duration)
    {
        $posts = DB::table('posts')
        ->where('created_at', '>=', Carbon::now()->subDays($duration))
        ->pluck('hashtags'); // Get only the hashtags column

        $hashtagCounts = [];

        foreach ($posts as $hashtags) {
            $tagsArray = explode(', ', $hashtags); // Split by comma
            foreach ($tagsArray as $tag) {
                $tag = trim($tag); // Remove spaces
                if ($tag) {
                    $hashtagCounts[$tag] = ($hashtagCounts[$tag] ?? 0) + 1;
                }
            }
        }

        // Sort hashtags by usage count (descending)
        arsort($hashtagCounts);

        // Return the top N hashtags
        return array_slice($hashtagCounts, 0, $limit, true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
