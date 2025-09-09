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
        Schema::table('point_de_ventes', function (Blueprint $table) {
            // On change la colonne 'type' pour qu'elle puisse accepter des chaînes de caractères plus longues
            $table->string('type', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_de_ventes', function (Blueprint $table) {
            // Optionnel : si vous voulez pouvoir annuler, vous pouvez remettre l'ancien type
            // Par exemple, si c'était un ENUM :
            // $table->enum('type', ['valeur1', 'valeur2'])->change();
            // Pour cet exemple, nous allons simplement le laisser tel quel.
        });
    }
};