<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('point_de_vente_id')->constrained('point_de_ventes')->cascadeOnDelete();
            $table->foreignId('unite_de_vente_id')->constrained('unite_de_ventes')->cascadeOnDelete();
            $table->integer('quantite_stock');
            $table->timestamps();
        });
    }

    public function down(): void
    {
       
        Schema::dropIfExists('inventories');
    }
};