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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            // CORRECTION 1 : On lie l'inventaire à une 'unite_de_vente_id' et non 'product_id'
            $table->foreignId('unite_de_vente_id')->constrained('unite_de_ventes')->cascadeOnDelete();
            
            $table->foreignId('point_de_vente_id')->constrained('point_de_ventes')->cascadeOnDelete();

            // CORRECTION 2 : On nomme la colonne 'quantite_stock' pour être cohérent
            $table->integer('quantite_stock')->default(0);
            
            $table->timestamps();

            // La clé unique doit maintenant être sur l'unité de vente et le point de vente
            $table->unique(['unite_de_vente_id', 'point_de_vente_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};