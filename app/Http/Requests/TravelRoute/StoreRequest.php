<?php

namespace App\Http\Requests\TravelRoute;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'required|string|max:150',
            'start_point' => 'required|string|max:150',
            'end_point' => 'required|string|max:150',
            'estimated_distance' => 'nullable|numeric|min:0|max:999999.99',
            'description' => 'nullable|string',
        ];
    }
}
