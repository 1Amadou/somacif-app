<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reglements', function (Blueprint $table) {
            // On vérifie si la colonne n'existe pas déjà avant de l'ajouter
            if (!Schema::hasColumn('reglements', 'point_de_vente_id')) {
                $table->foreignId('point_de_vente_id')->nullable()->after('client_id')->constrained('point_de_ventes');
            }
            if (!Schema::hasColumn('reglements', 'montant_calcule')) {
                $table->decimal('montant_calcule', 10, 2)->default(0)->after('montant_verse');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reglements', function (Blueprint $table) {
            // On vérifie que les colonnes existent avant de les supprimer
            if (Schema::hasColumn('reglements', 'point_de_vente_id')) {
                $table->dropForeign(['point_de_vente_id']);
                $table->dropColumn('point_de_vente_id');
            }
            if (Schema::hasColumn('reglements', 'montant_calcule')) {
                $table->dropColumn('montant_calcule');
            }
        });
    }
};