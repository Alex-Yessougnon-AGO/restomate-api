<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul un client connecté peut réserver
        return $this->user()->isClient();
    }

    public function rules(): array
    {
        return [
            'table_id'         => ['required', 'exists:tables,id'],
            // exists:tables,id → vérifie que cette table existe en BDD
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            // after_or_equal:today → pas de réservation dans le passé
            'start_time'       => ['required', 'date_format:H:i'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'table_id.required'         => 'Veuillez choisir une table.',
            'table_id.exists'           => 'Cette table n\'existe pas.',
            'reservation_date.required' => 'La date de réservation est obligatoire.',
            'reservation_date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur.',
            'start_time.required'       => 'L\'heure de réservation est obligatoire.',
            'number_of_guests.required' => 'Le nombre de personnes est obligatoire.',
            'number_of_guests.min'      => 'Il faut au moins 1 personne.',
        ];
    }
}