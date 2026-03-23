<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
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
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'type' => 'sometimes|in:preventive,corrective',
            'start_at' => 'sometimes|date',
            'closed_at' => 'sometimes|date|after:start_at',
            'description' => 'sometimes|string',
            'cost' => 'sometimes|numeric|min:0|max:99999999.99',
            'status' => 'sometimes|in:open,closed',
        ];
    }
}
