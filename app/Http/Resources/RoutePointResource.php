<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoutePointResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int) $this->id,
            'x_point' => (int) $this->x_point,
            'y_point' => (int) $this->y_point,
            'screen_width'=> (int) $this->screen_width,
            'screen_height'=> (int) $this->screen_height,
            'line_id' => (int) $this->line_id
        ];
    }
}
