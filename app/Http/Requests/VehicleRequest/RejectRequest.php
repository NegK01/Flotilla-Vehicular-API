<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\VehicleRequest;

class RejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observation' => 'sometimes|nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $vehicleRequest = $this->route('vehicleRequest');

            if ($vehicleRequest && $vehicleRequest->status !== VehicleRequest::STATUS_PENDING) {
                $validator->errors()->add(
                    'status',
                    'Solo se pueden rechazar solicitudes en estado pendiente.'
                );
            }
        });
    }
}
