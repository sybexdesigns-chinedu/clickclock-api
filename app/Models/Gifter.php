<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gifter extends Model
{
    protected $guarded = [];

    public function gifter()
    {
        return $this->belongsTo(User::class, 'gifter_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }
    public function livestream()
    {
        return $this->belongsTo(Livestream::class);
    }
}
