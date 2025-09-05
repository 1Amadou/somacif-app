<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reglements', function (Blueprint $table) {
            // On ajoute la colonne pour lier le règlement à la commande principale
            $table->foreignId('order_id')->nullable()->after('client_id')->constrained('orders');
        });
    }

    public function down(): void
    {
        Schema::table('reglements', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};