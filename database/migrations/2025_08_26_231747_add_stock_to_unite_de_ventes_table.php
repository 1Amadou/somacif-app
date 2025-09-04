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
        // Vérifie si la colonne 'stock' existe avant de la créer
        if (!Schema::hasColumn('unite_de_ventes', 'stock')) {
            Schema::table('unite_de_ventes', function (Blueprint $table) {
                $table->integer('stock')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vérifie si la colonne 'stock' existe avant de la supprimer
        if (Schema::hasColumn('unite_de_ventes', 'stock')) {
            Schema::table('unite_de_ventes', function (Blueprint $table) {
                $table->dropColumn('stock');
            });
        }
    }
};