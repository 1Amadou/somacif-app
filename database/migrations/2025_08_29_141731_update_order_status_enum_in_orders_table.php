<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // CORRECTION : Voici la liste finale et unifiée de tous les statuts possibles.
            $table->enum('statut', [
                'en_attente', 
                'validee', 
                'en_preparation', // Le statut qui manquait
                'en_cours_livraison', 
                'livree', 
                'annulee'
            ])->default('en_attente')->change();
        });
    }

    public function down(): void
    {
        // Logique pour revenir à un état précédent si nécessaire
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('statut', [
                'en_attente', 
                'validee', 
                'en_cours_de_livraison', 
                'livree', 
                'annulee'
            ])->default('en_attente')->change();
        });
    }
};