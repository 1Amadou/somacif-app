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
        Schema::table('arrivages', function (Blueprint $table) {
            // CORRECTION: On vérifie si la colonne 'statut' n'existe pas AVANT de l'ajouter.
            if (!Schema::hasColumn('arrivages', 'statut')) {
                $table->string('statut')->default('en_cours')->after('notes');
            }
            
            // On ajoute la nouvelle colonne pour la décharge
            if (!Schema::hasColumn('arrivages', 'decharge_signee_path')) {
                $table->string('decharge_signee_path')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arrivages', function (Blueprint $table) {
            // On vérifie que les colonnes existent avant de les supprimer pour éviter les erreurs
            if (Schema::hasColumn('arrivages', 'decharge_signee_path')) {
                $table->dropColumn('decharge_signee_path');
            }
            if (Schema::hasColumn('arrivages', 'statut')) {
                $table->dropColumn('statut');
            }
        });
    }
};