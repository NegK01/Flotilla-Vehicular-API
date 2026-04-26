<?php

namespace App\Http\Requests\TravelRoute;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:150',
            'start_point' => 'sometimes|string|max:150',
            'end_point' => 'sometimes|string|max:150',
            'estimated_distance' => 'sometimes|nullable|numeric|min:0|max:999999.99',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
