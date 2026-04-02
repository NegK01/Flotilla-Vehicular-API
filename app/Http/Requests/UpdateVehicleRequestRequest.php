<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequestRequest extends FormRequest
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
            'driver_id' => 'sometimes|exists:users,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'start_at' => 'sometimes|date|after:now',
            'end_at' => 'sometimes|date|after:start_at',
            'status' => 'sometimes|in:pending,approved,rejected,cancelled',
            'observation' => 'sometimes|nullable|string',
            'reviewed_by' => 'sometimes|exists:users,id',
            'reviewed_at' => 'sometimes|date',
            'request_type' => 'sometimes|in:driver_request,direct_assignment',
        ];
    }
}
