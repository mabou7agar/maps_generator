<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacesPoint extends Model
{
    use HasFactory;
    protected $guarded = [];
    const ERROR_MESSAGE = 'Place Not Exist';
}
