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
        Schema::create('vente_directe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_directe_id')->constrained()->onDelete('cascade');
            $table->foreignId('unite_de_vente_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('quantite');
            $table->unsignedBigInteger('prix_unitaire');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vente_directe_items');
    }
};