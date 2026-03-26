<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            // Informations du restaurant
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city', 100);
            $table->string('phone', 20)->nullable();

            // Horaires d'ouverture
            $table->time('opening_time')->default('08:00:00');
            $table->time('closing_time')->default('23:00:00');
            // ^ Ces deux champs permettent de vérifier
            // si une réservation est dans les horaires

            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            // ^ Ajoute la colonne deleted_at
            // Au lieu de supprimer, Laravel met une date ici
            // La donnée reste en BDD mais est "invisible"
            // C'est l'archivage professionnel

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};