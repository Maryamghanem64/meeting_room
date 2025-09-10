<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['meetingId', 'minute_id', 'filePath', 'fileType'];
    public $timestamps = true;

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meetingId');
    }

    public function minute()
    {
        return $this->belongsTo(MeetingMinute::class, 'minute_id');
    }
}
