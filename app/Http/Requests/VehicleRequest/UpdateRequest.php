<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\VehicleRequest;
use Illuminate\Support\Facades\DB;

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
            'vehicle_id'  => [
                'sometimes',
                Rule::exists('vehicles', 'id')
                    ->whereNull('deleted_at')
            ],
            'start_at'    => 'sometimes|date|after:now',
            'end_at'      => 'sometimes|date|after:start_at',
            'observation' => 'sometimes|nullable|string|max:500',
        ];
    }

    /**
     * Configure the validator instance.
     * Mantenemos el controlador limpio y delegamos la validación del solapamiento aquí.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $vehicleRequest = $this->route('vehicleRequest');

            if (
                $vehicleRequest &&
                ($this->has('start_at') || $this->has('end_at') || $this->has('vehicle_id'))
            ) {
                $data = $validator->getData();
                $checkVehicleId = $data['vehicle_id'] ?? $vehicleRequest->vehicle_id;
                $checkStartAt = $data['start_at'] ?? $vehicleRequest->start_at;
                $checkEndAt = $data['end_at'] ?? $vehicleRequest->end_at;

                $available = DB::selectOne('SELECT fn_is_vehicle_available(?, ?, ?, ?) AS available', [
                    $checkVehicleId,
                    $checkStartAt,
                    $checkEndAt,
                    $vehicleRequest->id // Excluir la solicitud actual
                ])->available;

                if (!$available) {
                    $validator->errors()->add(
                        'start_at',
                        'No se puede actualizar. El vehículo no está disponible para las nuevas fechas.'
                    );
                }
            }
        });
    }
}
