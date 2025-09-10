<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMinute extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'notes',
        'decisions',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function actionItems()
    {
        return $this->hasMany(ActionItem::class, 'minute_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'minute_id');
    }
}
