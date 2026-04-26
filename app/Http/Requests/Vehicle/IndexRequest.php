<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Vehicle;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    Vehicle::STATUS_AVAILABLE,
                    Vehicle::STATUS_RESERVED,
                    Vehicle::STATUS_MAINTENANCE,
                    Vehicle::STATUS_OUT_OF_SERVICE,
                ]),
            ],
            'trashed'  => ['nullable', 'in:only,with'],
            'start_at' => ['nullable', 'date', 'required_with:end_at'],
            'end_at'   => ['nullable', 'date', 'after_or_equal:start_at', 'required_with:start_at'],
        ];
    }
}
