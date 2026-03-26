<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    // Après - façon officielle Laravel
    public function authorize(): bool
    {
        return $this->user() !== null;
        // $this->user() retourne l'utilisateur connecté
        // Si null = pas connecté = refusé
    }

    public function rules(): array
    {
        return [
            'reservation_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'start_time'       => ['sometimes', 'date_format:H:i'],
            'number_of_guests' => ['sometimes', 'integer', 'min:1'],
            'special_requests' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}