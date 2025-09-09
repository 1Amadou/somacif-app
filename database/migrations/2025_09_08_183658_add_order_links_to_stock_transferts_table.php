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
    Schema::table('stock_transferts', function (Blueprint $table) {
        // Ajoute une colonne pour lier le transfert à la commande d'origine
        $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
        // Ajoute une colonne pour tracer la nouvelle commande créée
        $table->foreignId('new_order_id')->nullable()->constrained('orders')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transferts', function (Blueprint $table) {
            //
        });
    }
};
