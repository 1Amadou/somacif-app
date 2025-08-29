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
        Schema::table('point_de_ventes', function (Blueprint $table) {
            // On ajoute une colonne pour lier un responsable (client)
            // Elle peut être nulle si un point de vente n'a pas de responsable attitré
            $table->foreignId('responsable_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_de_ventes', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn('responsable_id');
        });
    }
};