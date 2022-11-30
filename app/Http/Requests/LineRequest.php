<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_intersection' => 'required|boolean',
            'floor' => 'required|int'
        ];
    }

    public function createArray(): array
    {
        return [
            'is_intersection' => $this->is_intersection,
            'floor' => $this->floor,
            'name' => $this->name,
        ];
    }
}
