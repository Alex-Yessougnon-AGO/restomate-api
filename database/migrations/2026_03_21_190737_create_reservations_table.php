<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Qui réserve ?
            $table->foreignId('client_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            // ^ On précise 'users' car le nom de la colonne
            // (client_id) ne correspond pas au nom de la table (users)
            // Laravel ne peut pas deviner seul

            // Quelle table ?
            $table->foreignId('table_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Quand ?
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            // ^ start_time + 1h30 par défaut
            // On calculera end_time dans le Controller

            // Combien de personnes ?
            $table->unsignedTinyInteger('number_of_guests');

            // Statut de la réservation
            $table->enum('status', [
                'pending',    // En attente de confirmation
                'confirmed',  // Confirmée par l'admin
                'cancelled',  // Annulée
                'no_show'     // Client ne s'est pas présenté
            ])->default('pending');

            // Demandes spéciales (allergies, anniversaire...)
            $table->text('special_requests')->nullable();

            // Quand a-t-elle été annulée ?
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};