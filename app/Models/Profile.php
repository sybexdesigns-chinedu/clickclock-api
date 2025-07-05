<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'is_private' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower(trim($value));
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower(trim($value)));
    }
}
