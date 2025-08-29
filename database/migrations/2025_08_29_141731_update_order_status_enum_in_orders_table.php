<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // On change la colonne pour utiliser la nouvelle liste de statuts
            $table->enum('statut', [
                'en_attente', 
                'validee', 
                'prete_pour_livraison', 
                'en_cours_de_livraison', 
                'livree', 
                'annulee'
            ])->default('en_attente')->change();
        });
    }

    public function down(): void
    {
        // Optionnel : revenir à l'ancien état si nécessaire
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('statut', ['en_attente', 'expediee', 'annulee'])->default('en_attente')->change();
        });
    }
};