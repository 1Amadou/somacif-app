<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unite_de_ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('nom_unite');
            $table->string('calibre')->nullable();
            
            // Prix de vente
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('prix_grossiste', 10, 2)->nullable();
            $table->decimal('prix_hotel_restaurant', 10, 2)->nullable();
            $table->decimal('prix_particulier', 10, 2)->nullable();

            // CORRECTION : Ajout du coût d'achat directement dans la structure principale.
            $table->decimal('prix_interne', 10, 2)->default(0.00)->comment('Coût d\'achat pour le calcul de la marge');

            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unite_de_ventes');
    }
};