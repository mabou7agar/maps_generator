<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlacesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'x_point' => 'required|int',
            'y_point' => 'required|int',
            'floor' => 'required|int',
            'name' => 'required',
            'code' => 'required'
        ];
    }

    public function createArray()
    {
        return [
            'x_point' => $this->x_point,
            'y_point' => $this->y_point,
            'floor' => $this->floor,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type ?? 'SHOP',
            'screen_width'=> (int) $this->screen_width,
            'screen_height'=> (int) $this->screen_height,
        ];
    }
}
