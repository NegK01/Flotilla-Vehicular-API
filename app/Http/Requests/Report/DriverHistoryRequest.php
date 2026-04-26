<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class DriverHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $driver = $this->route('driver');

            if ($driver && $driver->role_id !== 3) {
                $validator->errors()->add(
                    'driver',
                    'Chofer no encontrado o el usuario no es chofer.'
                );
            }
        });
    }
}
