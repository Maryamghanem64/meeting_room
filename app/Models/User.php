<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory,Notifiable;
   protected $primaryKey = 'Id';
public $incrementing = true;
protected $keyType = 'int';

    protected $fillable = ['name', 'email', 'password'];
    public $timestamps = true;
    protected $hidden = ['password','remembe_token'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->withPivot('Id');
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
