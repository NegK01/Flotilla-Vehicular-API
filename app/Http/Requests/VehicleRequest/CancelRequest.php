<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\VehicleRequest;
use Illuminate\Validation\Validator;

class CancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    /**
     * Extraemos la validación de estado del controlador.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $vehicleRequest = $this->route('vehicleRequest');

            if ($vehicleRequest && !in_array($vehicleRequest->status, [
                VehicleRequest::STATUS_PENDING,
                VehicleRequest::STATUS_APPROVED,
            ], true)) {
                $validator->errors()->add(
                    'status',
                    'Solo se pueden cancelar solicitudes en estado pendiente o aprobado.'
                );
            }
        });
    }
}
