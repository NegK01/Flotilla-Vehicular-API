<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DirectAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id'   => 'required|exists:users,id',
            'vehicle_id'  => 'required|exists:vehicles,id',
            'start_at'    => 'required|date|after:now',
            'end_at'      => 'required|date|after:start_at',
            'observation' => 'nullable|string|max:500',
        ];
    }
}
