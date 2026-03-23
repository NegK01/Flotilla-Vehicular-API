<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
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
            'vehicle_request_id' => 'nullable|exists:vehicle_requests,id',
            'driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'travel_route_id' => 'nullable|exists:travel_routes,id',
            'departure_at' => 'required|date',
            'return_at' => 'nullable|date|after:departure_at',
            'departure_mileage' => 'required|integer|min:0',
            'return_mileage' => 'nullable|integer|min:departure_mileage',
            'observations' => 'nullable|string',
        ];
    }
}
