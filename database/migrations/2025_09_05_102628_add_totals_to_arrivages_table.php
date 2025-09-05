<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arrivages', function (Blueprint $table) {
            // On ajoute les colonnes manquantes après 'details_produits'
            $table->decimal('montant_total_arrivage', 10, 2)->default(0)->after('details_produits');
            $table->integer('total_quantite')->default(0)->after('montant_total_arrivage');
        });
    }

    public function down(): void
    {
        Schema::table('arrivages', function (Blueprint $table) {
            $table->dropColumn(['montant_total_arrivage', 'total_quantite']);
        });
    }
};