<?php

namespace App\Services;

use App\Models\Table;
use App\Models\Restaurant;
use Carbon\Carbon;

class AvailabilityService
{
    // Durée standard d'un repas en minutes
    const MEAL_DURATION = 90;

    /**
     * Trouve toutes les tables disponibles pour un créneau donné
     *
     * @param int    $restaurantId  L'ID du restaurant
     * @param string $date          La date (ex: "2026-03-25")
     * @param string $startTime     L'heure de début (ex: "19:30")
     * @param int    $guests        Nombre de personnes
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableTables(
        int $restaurantId,
        string $date,
        string $startTime,
        int $guests
    ) {
        // Calcule l'heure de fin automatiquement
        $endTime = Carbon::parse($startTime)
                         ->addMinutes(self::MEAL_DURATION)
                         ->format('H:i');

        // Récupère les tables qui respectent TOUTES ces conditions :
        return Table::where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->where('capacity', '>=', $guests)
            // ^ La table doit pouvoir accueillir ce nombre de personnes

            ->whereDoesntHave('reservations', function ($query) use ($date, $startTime, $endTime) {
                // whereDoesntHave = "qui N'ONT PAS de réservation qui..."
                $query->where('reservation_date', $date)
                      ->whereIn('status', ['pending', 'confirmed'])
                      // ^ On ignore les réservations annulées ou no_show

                      ->where(function ($q) use ($startTime, $endTime) {
                          // Chevauchement de créneaux :
                          // Une collision existe si le nouveau créneau
                          // commence AVANT que l'autre finisse
                          // ET finit APRÈS que l'autre commence
                          $q->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                      });
            })
            ->with('restaurant')
            // ^ Charge les données du restaurant en même temps
            // Évite le problème N+1 (on en parlera dans le résumé final)
            ->get();
    }

    /**
     * Vérifie si UNE table spécifique est disponible
     *
     * @param int    $tableId    L'ID de la table
     * @param string $date       La date
     * @param string $startTime  L'heure de début
     * @return bool
     */
    public function isTableAvailable(
        int $tableId,
        string $date,
        string $startTime
    ): bool {
        $endTime = Carbon::parse($startTime)
                         ->addMinutes(self::MEAL_DURATION)
                         ->format('H:i');

        $conflictingReservation = \App\Models\Reservation::where('table_id', $tableId)
            ->where('reservation_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
        // exists() → retourne true/false au lieu de récupérer les données
        // C'est plus rapide car MySQL s'arrête dès qu'il trouve 1 résultat

        return !$conflictingReservation;
    }

    /**
     * Calcule l'heure de fin à partir de l'heure de début
     */
    public function calculateEndTime(string $startTime): string
    {
        return Carbon::parse($startTime)
                     ->addMinutes(self::MEAL_DURATION)
                     ->format('H:i:s');
    }
}