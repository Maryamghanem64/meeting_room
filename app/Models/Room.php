<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'capacity'];
    public $timestamps = true;

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'feature_room', 'roomId', 'featureId');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class, 'roomId');
    }
}
