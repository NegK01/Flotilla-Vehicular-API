<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequest extends FormRequest
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
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:preventive,corrective',
            'start_at' => 'required|date',
            'closed_at' => 'nullable|date|after:start_at',
            'description' => 'required|string',
            'cost' => 'nullable|numeric|min:0|max:99999999.99',
            'status' => 'nullable|in:open,closed',
        ];
    }
}
