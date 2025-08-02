<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingAttendee extends Model
{
    use HasFactory;

    protected $fillable = ['meetingId', 'userId', 'isPresent'];
    public $timestamps = false;

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meetingId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
