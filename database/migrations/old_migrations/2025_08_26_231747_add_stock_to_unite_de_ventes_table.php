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
        Schema::table('unite_de_ventes', function (Blueprint $table) {
            // CORRECTION : On retire ->after('prix_unitaire') pour simplement
            // ajouter la colonne à la fin, ce qui évite l'erreur.
            $table->integer('stock')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unite_de_ventes', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};