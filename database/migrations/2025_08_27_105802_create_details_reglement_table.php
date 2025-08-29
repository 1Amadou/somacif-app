<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('details_reglement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reglement_id')->constrained('reglements')->cascadeOnDelete();
            $table->foreignId('unite_de_vente_id')->constrained('unite_de_ventes');
            $table->integer('quantite_vendue');
            $table->decimal('prix_de_vente_unitaire', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('details_reglement');
    }
};