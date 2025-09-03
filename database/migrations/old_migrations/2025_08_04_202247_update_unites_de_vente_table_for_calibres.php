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
            $table->string('calibre')->after('nom_unite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unite_de_ventes', function (Blueprint $table) {
            $table->dropColumn('calibre');
        });
    }
};