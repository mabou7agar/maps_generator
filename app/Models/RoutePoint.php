<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutePoint extends Model
{
    use HasFactory;
    protected $guarded = [];
    const ERROR_MESSAGE = 'Route not exist';

    public function line(){
        return$this->belongsTo(Line::class);
    }
}
