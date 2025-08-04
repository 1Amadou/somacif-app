<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('origine')->nullable()->after('calibres');
            $table->string('poids_moyen')->nullable()->after('origine');
            $table->string('conservation')->nullable()->after('poids_moyen');
            $table->longText('infos_nutritionnelles')->nullable()->after('description_longue');
            $table->longText('idee_recette')->nullable()->after('infos_nutritionnelles');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['origine', 'poids_moyen', 'conservation', 'infos_nutritionnelles', 'idee_recette']);
        });
    }
};