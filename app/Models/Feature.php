<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model

{ use HasFactory;
    public $timestamps=false;
   protected $table='features';
    protected $primaryKey='Id';
    protected $fillable = ['name'];
}
