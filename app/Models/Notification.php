<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        return $this->update(['is_read' => true]);
    }

    //move to user model if needed
    // public function markAllAsRead()
    // {
    //     return $this->update(['is_read' => true]);
    // }

}
