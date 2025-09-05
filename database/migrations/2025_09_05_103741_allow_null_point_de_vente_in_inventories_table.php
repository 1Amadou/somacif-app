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
        Schema::table('inventories', function (Blueprint $table) {
            // On autorise la colonne à être NULL pour représenter l'entrepôt principal
            $table->foreignId('point_de_vente_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Action inverse (ne devrait pas être nécessaire en production)
            $table->foreignId('point_de_vente_id')->nullable(false)->change();
        });
    }
};