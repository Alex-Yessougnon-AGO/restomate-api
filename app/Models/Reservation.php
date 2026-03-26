<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'table_id',
        'reservation_date',
        'start_time',
        'end_time',
        'number_of_guests',
        'status',
        'special_requests',
        'cancelled_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'cancelled_at'     => 'datetime',
        'number_of_guests' => 'integer',
    ];

    // ═══════════════════════════════
    // RELATIONS
    // ═══════════════════════════════

    /**
     * Une réservation appartient à un client
     * Utilisation : $reservation->client
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
        // On précise 'client_id' car le nom de la méthode
        // est 'client' et non 'user' — Laravel ne peut pas deviner
    }

    /**
     * Une réservation concerne une table
     * Utilisation : $reservation->table
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    // ═══════════════════════════════
    // HELPERS MÉTIER
    // ═══════════════════════════════

    /**
     * Vérifier si une réservation peut être annulée
     * Règle métier : seulement si status est pending ou confirmed
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Vérifier si la réservation appartient à un client donné
     * Utilisation : $reservation->belongsToClient($userId)
     */
    public function belongsToClient(int $userId): bool
    {
        return $this->client_id === $userId;
    }
}