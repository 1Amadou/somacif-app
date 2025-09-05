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
            $table->dropForeign(['unite_de_vente_id']);
            $table->dropColumn(['unite_de_vente_id', 'quantite']);
            $table->json('details')->after('destination_point_de_vente_id');
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
