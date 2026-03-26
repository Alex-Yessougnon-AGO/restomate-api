<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            // 'sometimes' = seulement si le champ est présent dans la requête
            // Permet la mise à jour partielle (PATCH)
            'name'         => ['sometimes', 'string', 'max:255'],
            'description'  => ['sometimes', 'nullable', 'string'],
            'address'      => ['sometimes', 'string'],
            'city'         => ['sometimes', 'string', 'max:100'],
            'phone'        => ['sometimes', 'nullable', 'string', 'max:20'],
            'opening_time' => ['sometimes', 'date_format:H:i'],
            'closing_time' => ['sometimes', 'date_format:H:i'],
            'is_active'    => ['sometimes', 'boolean'],
        ];
    }
}