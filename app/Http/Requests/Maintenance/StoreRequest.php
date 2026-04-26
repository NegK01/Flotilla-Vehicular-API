<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Vehicle;

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
            'vehicle_id'  => [
                'required',
                Rule::exists('vehicles', 'id')
                    ->whereNull('deleted_at')
            ],
            'type' => 'required|in:preventive,corrective',
            'start_at' => 'required|date',
            'description' => 'required|string',
        ];
    }
}
