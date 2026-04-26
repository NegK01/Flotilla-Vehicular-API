<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Vehicle;

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
            'plate' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('vehicles', 'plate')
                    ->ignore($this->route('vehicle')?->id)
                    ->whereNull('deleted_at'),
            ],
            'brand' => 'sometimes|string|max:100',
            'model' => 'sometimes|string|max:100',
            'year' => 'sometimes|integer|min:1900|max:2100',
            'vehicle_type' => 'sometimes|string|max:50',
            'capacity' => 'sometimes|integer|min:1|max:255',
            'fuel_type' => 'sometimes|string|max:50',
            'image_path' => 'sometimes|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => [
                'sometimes',
                Rule::in([Vehicle::STATUS_AVAILABLE, Vehicle::STATUS_OUT_OF_SERVICE]),
            ],
            'current_mileage' => 'sometimes|integer|min:0',
        ];
    }
}
