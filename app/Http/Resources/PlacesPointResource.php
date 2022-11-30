<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlacesPointResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'x_point' => (int) $this->x_point,
            'y_point' => (int) $this->y_point,
            'floor' => (int) $this->floor,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'screen_width'=> (int) $this->screen_width,
            'screen_height'=> (int) $this->screen_height,

        ];
    }
}
