<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId', 'roomId', 'title', 'agenda',
        'startTime', 'endTime', 'type', 'status'
    ];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'roomId');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'meetingId');
    }

    public function attendees()
    {
        return $this->hasMany(MeetingAttendee::class, 'meetingId');
    }

    public function minutes()
    {
        return $this->hasMany(MeetingMinute::class, 'meetingId');
    }
}
