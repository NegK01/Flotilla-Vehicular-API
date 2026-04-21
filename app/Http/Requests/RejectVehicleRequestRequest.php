<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectVehicleRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'observation' => 'nullable|string|max:500',
        ];
    }
}
