<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Pour identifier si c'est une vente directe
            $table->boolean('is_vente_directe')->default(false)->after('id');
            // Pour savoir de quel point de vente le stock a été pris
            $table->foreignId('point_de_vente_id')->nullable()->after('client_id')->constrained('point_de_ventes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_vente_directe', 'point_de_vente_id']);
        });
    }
};