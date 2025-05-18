<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\profile;
use App\Models\File;
use App\Models\EmailVerification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //   One-to-One  Profile
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function emailVerification(): HasOne
    {
        return $this->hasOne(EmailVerification::class);
    }
    /*
    public function otps()
    {
        return $this->hasMany(Otp::class);
    }*/
}
