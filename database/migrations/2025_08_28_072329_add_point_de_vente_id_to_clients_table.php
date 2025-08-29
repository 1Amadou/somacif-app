<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Un client peut être lié à un point de vente.
            // La colonne est unique pour s'assurer qu'un point de vente n'est assigné qu'à un seul client.
            $table->foreignId('point_de_vente_id')->nullable()->unique()->constrained('point_de_ventes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['point_de_vente_id']);
            $table->dropColumn('point_de_vente_id');
        });
    }
};