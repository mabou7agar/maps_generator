<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LineResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'floor' => $this->floor,
            'is_intersection' => (bool) $this->is_intersection,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
