<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // On supprime la contrainte et la colonne ajoutées par erreur
            if (Schema::hasColumn('clients', 'point_de_vente_id')) {
                // Le nom de la contrainte peut varier, on tente une suppression générique
                try {
                    $table->dropForeign(['point_de_vente_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('point_de_vente_id');
            }
        });
    }

    public function down(): void
    {
        // Pas d'action nécessaire pour le retour en arrière
    }
};