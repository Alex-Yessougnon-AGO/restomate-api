<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_id')
                  ->constrained()
                  ->cascadeOnDelete();
            // ^ foreignId crée la colonne restaurant_id (clé étrangère)
            // constrained() dit "cette valeur doit exister dans la table restaurants"
            // cascadeOnDelete() dit "si le restaurant est supprimé,
            // supprime aussi ses tables automatiquement"

            $table->string('name');
            // ^ Exemple : "Table 1", "Terrasse A", "VIP 3"

            $table->unsignedTinyInteger('capacity');
            // ^ unsignedTinyInteger = entier positif entre 0 et 255
            // Parfait pour le nombre de couverts

            $table->enum('location', [
                'intérieur',
                'terrasse',
                'bar',
                'privé'
            ])->default('intérieur');

            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};