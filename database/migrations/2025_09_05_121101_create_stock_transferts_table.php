<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transferts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unite_de_vente_id')->constrained('unite_de_ventes');
            $table->foreignId('source_point_de_vente_id')->constrained('point_de_ventes');
            $table->foreignId('destination_point_de_vente_id')->constrained('point_de_ventes');
            $table->decimal('quantite', 10, 2);
            $table->foreignId('user_id')->comment('Admin qui a effectué le transfert')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transferts');
    }
};