<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // HasApiTokens → permet à Sanctum de générer des tokens
    // HasFactory   → permet de créer des faux users pour les tests
    // Notifiable   → permet d'envoyer des notifications

    // ✅ Champs qu'on autorise à remplir en masse
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    // 🔒 Champs qu'on cache dans les réponses JSON
    // (on ne veut jamais envoyer le mot de passe dans une réponse API !)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 🔄 Conversions automatiques de types
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed', // hashé automatiquement
            'is_active'         => 'boolean',
        ];
    }

    // ═══════════════════════════════
    // RELATIONS
    // ═══════════════════════════════

    /**
     * Un client peut avoir plusieurs réservations
     * Utilisation : $user->reservations
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'client_id');
    }

    // ═══════════════════════════════
    // HELPERS MÉTIER
    // ═══════════════════════════════

    /**
     * Vérifier si l'utilisateur est admin
     * Utilisation : if ($user->isAdmin()) { ... }
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est client
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }
}