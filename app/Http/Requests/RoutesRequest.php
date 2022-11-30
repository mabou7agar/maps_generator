<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoutesRequest extends FormRequest
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
            'line_id' => 'required|int',
            'screen_width'=>'required',
            'screen_height'=>'required',
        ];
    }

    public function createArray()
    {
        return [
            'x_point' => $this->x_point,
            'y_point' => $this->y_point,
            'screen_width' => (int) $this->screen_width ?? 142,
            'screen_height' => (int) $this->screen_height ?? 368,
            'line_id' => $this->line_id
        ];
    }
}
