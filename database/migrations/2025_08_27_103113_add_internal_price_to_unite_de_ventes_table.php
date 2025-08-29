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
        Schema::table('unite_de_ventes', function (Blueprint $table) {
            // On vérifie si la colonne n'existe pas déjà avant de l'ajouter
            if (!Schema::hasColumn('unite_de_ventes', 'calibre')) {
                $table->string('calibre')->after('product_id');
            }
            if (!Schema::hasColumn('unite_de_ventes', 'prix_unitaire')) {
                $table->decimal('prix_unitaire', 10, 2)->default(0)->after('prix_particulier');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unite_de_ventes', function (Blueprint $table) {
            $table->dropColumn(['calibre', 'prix_unitaire']);
        });
    }
};