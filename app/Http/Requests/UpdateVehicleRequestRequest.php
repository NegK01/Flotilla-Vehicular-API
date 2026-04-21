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
            'vehicle_id'  => 'sometimes|exists:vehicles,id',
            'start_at'    => 'sometimes|date|after:now',
            'end_at'      => 'sometimes|date|after:start_at',
            'observation' => 'sometimes|nullable|string|max:500',
        ];
    }
}
