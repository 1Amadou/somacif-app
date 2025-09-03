<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arrivages', function (Blueprint $table) {
            // On renomme l'ancienne colonne et on la transforme en clé étrangère
            $table->renameColumn('fournisseur', 'fournisseur_id_old');
        });
        Schema::table('arrivages', function (Blueprint $table) {
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('arrivages', function (Blueprint $table) {
            // ... (logique pour revenir en arrière)
        });
    }
};