<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = ['name', 'email', 'pwd'];
    public $timestamps = true;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'userId');
    }

    public function meetingAttendees()
    {
        return $this->hasMany(MeetingAttendee::class, 'userId');
    }
}
