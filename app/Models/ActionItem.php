<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'minute_id',
        'assigned_to',
        'description',
        'status',
        'due_date'
    ];

    public function minute()
    {
        return $this->belongsTo(MeetingMinute::class, 'minute_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
