<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'phone_number',
        'password',
        'verification_code',
        'is_verified'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
