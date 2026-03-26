<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // Qui a le droit d'utiliser cette requête ?
    // true = tout le monde (c'est public — n'importe qui peut s'inscrire)
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            // unique:users,email → vérifie que cet email n'existe pas déjà
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // confirmed → vérifie que password == password_confirmation
            'phone'    => ['nullable', 'string', 'max:20'],
        ];
    }

    // Messages d'erreur personnalisés en français
    public function messages(): array
    {
        return [
            'name.required'     => 'Le nom est obligatoire.',
            'email.required'    => 'L\'email est obligatoire.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit avoir au moins 8 caractères.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ];
    }
}