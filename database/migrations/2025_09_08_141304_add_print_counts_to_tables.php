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
    Schema::table('orders', function (Blueprint $table) {
        $table->unsignedInteger('facture_proforma_print_count')->default(0);
        $table->unsignedInteger('bon_livraison_print_count')->default(0);
    });

    Schema::table('reglements', function (Blueprint $table) {
        $table->unsignedInteger('recu_versement_print_count')->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
