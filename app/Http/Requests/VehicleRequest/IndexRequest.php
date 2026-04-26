<?php

namespace App\Http\Requests\VehicleRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\VehicleRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date'   => ['nullable', 'date', 'gte:start_date', 'required_with:start_date'],
            'request_type' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    VehicleRequest::TYPE_DRIVER_REQUEST,
                    VehicleRequest::TYPE_DIRECT_ASSIGNMENT,
                ]),
            ],
            'status' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    VehicleRequest::STATUS_PENDING,
                    VehicleRequest::STATUS_APPROVED,
                    VehicleRequest::STATUS_REJECTED,
                    VehicleRequest::STATUS_CANCELLED,
                ]),
            ],
            'trashed' => ['nullable', 'in:only,with'],
        ];
    }
}
