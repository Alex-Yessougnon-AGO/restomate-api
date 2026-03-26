<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'capacity'  => ['sometimes', 'integer', 'min:1', 'max:20'],
            'location'  => ['sometimes', 'in:intérieur,terrasse,bar,privé'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}