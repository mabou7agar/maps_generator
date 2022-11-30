<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Line extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function routes(): HasMany
    {
        return $this->hasMany(RoutePoint::class);
    }

    public function hasIntersections(): bool
    {
        return $this->hasMany(LineIntersection::class)->exists();
    }
}
