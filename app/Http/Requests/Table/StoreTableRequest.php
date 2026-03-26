<?php

namespace App\Http\Requests\Table;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'location' => ['required', 'in:intérieur,terrasse,bar,privé'],
            // in: → vérifie que la valeur est dans la liste autorisée
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom de la table est obligatoire.',
            'capacity.required' => 'La capacité est obligatoire.',
            'capacity.min'      => 'La capacité doit être d\'au moins 1 personne.',
            'location.in'       => 'La localisation doit être : intérieur, terrasse, bar ou privé.',
        ];
    }
}