<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class DirectAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id'   => [
                'required',
                Rule::exists('users', 'id')
                    ->where('role_id', 3)
                    ->whereNull('deleted_at')
            ],
            'vehicle_id'  => [
                'required',
                Rule::exists('vehicles', 'id')
                    ->whereNull('deleted_at')
            ],
            'start_at'    => 'required|date|after:now',
            'end_at'      => 'required|date|after:start_at',
            'observation' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $vehicleId = $this->input('vehicle_id');
            $startAt   = $this->input('start_at');
            $endAt     = $this->input('end_at');

            if ($vehicleId && $startAt && $endAt && !$validator->errors()->has('start_at') && !$validator->errors()->has('end_at')) {
                $available = DB::selectOne(
                    "SELECT fn_is_vehicle_available(?, ?::timestamp, ?::timestamp) AS is_available",
                    [$vehicleId, $startAt, $endAt]
                )->is_available;

                if (!$available) {
                    $validator->errors()->add(
                        'vehicle_id',
                        'El vehículo seleccionado no está disponible (o tiene solapamiento de fechas) para el rango solicitado.'
                    );
                }
            }
        });
    }
}
