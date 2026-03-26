<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Table extends Model
{
    use HasFactory, SoftDeletes;

    // On précise explicitement le nom de la table BDD
    // car "Table" est un mot réservé — on évite toute confusion
    protected $table = 'tables';

    protected $fillable = [
        'restaurant_id',
        'name',
        'capacity',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity'  => 'integer',
    ];

    // ═══════════════════════════════
    // RELATIONS
    // ═══════════════════════════════

    /**
     * Une table appartient à un restaurant
     * Utilisation : $table->restaurant
     * 
     * belongsTo = l'inverse de hasMany
     * Cette table CONTIENT la clé étrangère (restaurant_id)
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Une table a plusieurs réservations dans le temps
     * Utilisation : $table->reservations
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}