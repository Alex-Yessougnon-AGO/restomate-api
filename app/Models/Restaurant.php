<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory, SoftDeletes;
    // SoftDeletes → active l'archivage automatique
    // Quand tu appelles $restaurant->delete(),
    // Laravel met deleted_at = maintenant au lieu de supprimer

    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'phone',
        'opening_time',
        'closing_time',
        'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'opening_time' => 'string',
        'closing_time' => 'string',
    ];

    // ═══════════════════════════════
    // RELATIONS
    // ═══════════════════════════════

    /**
     * Un restaurant a plusieurs tables
     * Utilisation : $restaurant->tables
     */
    public function tables()
    {
        return $this->hasMany(Table::class);
    }

    /**
     * Un restaurant a plusieurs tables ACTIVES seulement
     * Utilisation : $restaurant->activeTables
     * 
     * Pourquoi cette relation séparée ?
     * Car quand on cherche une table disponible,
     * on ne veut jamais proposer une table désactivée
     */
    public function activeTables()
    {
        return $this->hasMany(Table::class)
                    ->where('is_active', true);
    }

    /**
     * Un restaurant a plusieurs réservations (via ses tables)
     * Utilisation : $restaurant->reservations
     */
    public function reservations()
    {
        return $this->hasManyThrough(
            Reservation::class,
            Table::class
        );
        // hasManyThrough = relation à travers une autre table
        // Restaurant → Tables → Reservations
    }
}