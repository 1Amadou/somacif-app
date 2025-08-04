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
        Schema::create('unite_de_ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('nom_unite'); // e.g., 'Carton', 'Pièce', 'Kg'
            $table->decimal('prix_grossiste', 10, 2);
            $table->decimal('prix_hotel_restaurant', 10, 2);
            $table->decimal('prix_particulier', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unite_de_ventes');
    }
};