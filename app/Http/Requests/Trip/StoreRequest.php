<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\VehicleRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\Trip;

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
            'vehicle_request_id' => [
                'required',
                Rule::exists('vehicle_requests', 'id')
                    ->whereNull('deleted_at'),
            ],
            'travel_route_id' => [
                'nullable',
                Rule::exists('travel_routes', 'id')
                    ->whereNull('deleted_at'),
            ],
            'departure_at' => 'required|date',
            'observations' => 'nullable|string',
        ];
    }

    /**
     * Solo se valida que la solicitud asociada esté aprobada y que el vehículo
     * no tenga ya un viaje activo en curso.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('vehicle_request_id')) {
                return;
            }

            $vehicleRequest = VehicleRequest::find($this->input('vehicle_request_id'));

            if ($vehicleRequest && $vehicleRequest->status !== VehicleRequest::STATUS_APPROVED) {
                $validator->errors()->add(
                    'vehicle_request_id',
                    'Solo se puede registrar viajes con solicitudes aprobadas.'
                );
                return;
            }

            // Un vehículo físico solo puede estar en un viaje a la vez
            $activeTrip = Trip::where('vehicle_id', $vehicleRequest->vehicle_id)
                ->whereNull('return_mileage')
                ->whereNull('deleted_at')
                ->first();

            if ($activeTrip) {
                $validator->errors()->add(
                    'vehicle_request_id',
                    "El vehículo ya se encuentra en un viaje activo. Debe de concluir el viaje #{$activeTrip->id} antes de iniciar uno nuevo."
                );
            }
        });
    }
}
