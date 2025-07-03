<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class livestream extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_live' => 'boolean',
        'is_creator' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
{
    return $this->belongsTo(Livestream::class, 'parent_livestream_id');
}

    public function children()
    {
        return $this->hasMany(Livestream::class, 'parent_livestream_id');
    }

    public function gifters()
    {
        return $this->hasMany(Gifter::class);
    }

    public function comments()
    {
        return $this->hasMany(LivestreamComment::class)->orderByDesc('id');
    }

    public function viewers()
    {
        return $this->hasMany(Viewer::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

}
