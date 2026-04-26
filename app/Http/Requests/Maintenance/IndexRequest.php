<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Maintenance;

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
            'type' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    Maintenance::TYPE_PREVENTIVE,
                    Maintenance::TYPE_CORRECTIVE,
                ]),
            ],
            'status' => [
                'nullable',
                'string',
                'in:' . implode(',', [
                    Maintenance::STATUS_OPEN,
                    Maintenance::STATUS_CLOSED,
                ]),
            ],
            'trashed' => ['nullable', 'in:only,with'],
        ];
    }
}
