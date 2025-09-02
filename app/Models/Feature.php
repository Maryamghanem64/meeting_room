<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $primaryKey = 'Id';
    protected $fillable = ['name'];
    public $timestamps = false;

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'feature_room', 'featureId', 'roomId');
    }
}
?>
