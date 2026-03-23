<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'full_name' => 'sometimes|string|max:150',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $this->route('user'),
            'phone' => 'sometimes|nullable|string|max:20',
            'role_id' => 'sometimes|exists:roles,id',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ];
    }
}
