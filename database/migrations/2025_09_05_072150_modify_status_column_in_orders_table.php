<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // On change le type de la colonne pour un 'string' et on définit la valeur par défaut
            $table->string('statut')->default('en_attente')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Action inverse si on doit annuler la migration
             $table->enum('statut', ['Reçue', 'Validée', 'En préparation', 'Expédiée', 'Livrée', 'Annulée'])->default('Reçue')->change();
        });
    }
};