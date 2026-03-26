<?php

namespace App\Http\Requests\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul l'admin peut créer un restaurant
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'address'      => ['required', 'string'],
            'city'         => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            // after:opening_time → fermeture doit être après ouverture
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Le nom du restaurant est obligatoire.',
            'address.required'      => 'L\'adresse est obligatoire.',
            'city.required'         => 'La ville est obligatoire.',
            'opening_time.required' => 'L\'heure d\'ouverture est obligatoire.',
            'closing_time.after'    => 'L\'heure de fermeture doit être après l\'ouverture.',
        ];
    }
}