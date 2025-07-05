<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_social' => 'boolean'
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function reward()
    {
        return $this->hasOne(Reward::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function livestreams()
    {
        return $this->hasMany(Livestream::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follower_following', 'following_id', 'follower_id')->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follower_following', 'follower_id', 'following_id')->withTimestamps();
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interest');
    }
}
