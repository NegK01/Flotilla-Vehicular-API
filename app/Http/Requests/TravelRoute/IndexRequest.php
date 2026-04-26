<?php

namespace App\Http\Requests\TravelRoute;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trashed' => ['nullable', 'in:only,with'],
        ];
    }
}
