<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viewer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_watching' => 'boolean'
    ];

    public function livestream()
    {
        return $this->belongsTo(livestream::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
