<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
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
            'vehicle_request_id' => 'sometimes|exists:vehicle_requests,id',
            'travel_route_id' => 'sometimes|nullable|exists:travel_routes,id',
            'departure_at' => 'sometimes|date',
            'return_at' => 'sometimes|nullable|date|after:departure_at',
            'departure_mileage' => 'sometimes|integer|min:0',
            'return_mileage' => 'sometimes|nullable|integer|gte:departure_mileage',
            'observations' => 'sometimes|nullable|string',
        ];
    }
}
