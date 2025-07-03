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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = strtolower(trim($value));
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['firstname'] = ucwords(strtolower(trim($value)));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['lastname'] = ucwords(strtolower(trim($value)));
    }
}
