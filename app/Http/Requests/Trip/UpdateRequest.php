<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Merge existing trip data so dependent validation rules (after, gte)
     * can compare against DB state when only partial fields are sent.
     */
    protected function prepareForValidation(): void
    {
        $trip = $this->route('trip');

        if ($trip) {
            $this->merge([
                'departure_at'      => $this->input('departure_at', $trip->departure_at),
                'return_at'         => $this->input('return_at', $trip->return_at),
                'departure_mileage' => $this->input('departure_mileage', $trip->departure_mileage),
            ]);
        }
    }

    /**
     * Campos editables tras la creación del viaje.
     * vehicle_request_id y departure_mileage son inmutables (derivados en store).
     * return_mileage solo se puede establecer una vez (protegido en withValidator).
     */
    public function rules(): array
    {
        return [
            'travel_route_id' => [
                'sometimes',
                'nullable',
                Rule::exists('travel_routes', 'id')
                    ->whereNull('deleted_at'),
            ],
            'departure_at'    => 'sometimes|date',
            'return_at'       => 'sometimes|nullable|date|after:departure_at',
            'return_mileage'  => 'sometimes|nullable|integer|gte:departure_mileage',
            'observations'    => 'sometimes|nullable|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $trip = $this->route('trip');

            if (!$trip) {
                return;
            }

            // Al registrar el retorno por primera vez, return_at y return_mileage son obligatorios.
            // Luego las correcciones posteriores son independientes donde solo se puede modificar return_at.
            if ($trip->return_at === null && $trip->return_mileage === null) {

                if ($this->filled('return_at') && !$this->filled('return_mileage')) {
                    $validator->errors()->add(
                        'return_mileage',
                        'Al registrar la fecha de retorno debe incluir también el kilometraje de regreso.'
                    );
                }

                if ($this->filled('return_mileage') && !$this->filled('return_at')) {
                    $validator->errors()->add(
                        'return_at',
                        'Al registrar el kilometraje de retorno debe incluir también la fecha de regreso.'
                    );
                }
            }

            // return_mileage es inmutable una vez registrado.
            // El trigger ya disparó y actualizó el odómetro del vehículo.
            // Cualquier corrección posterior debe hacerse por el Admin directamente en BD.
            if ($trip->return_mileage !== null && $this->has('return_mileage')) {
                $validator->errors()->add(
                    'return_mileage',
                    'El kilometraje de retorno ya fue registrado y no puede modificarse.'
                );
                return;
            }
        });
    }
}
