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
        Schema::create('point_de_ventes', function (Blueprint $table) {
            $table->id();
            // LIGNE CORRIGÉE : Ajout de la clé étrangère pour lier au client
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->enum('type', ['Principal', 'Secondaire', 'Partenaire']);
            $table->string('adresse');
            $table->string('telephone')->nullable();
            $table->string('horaires')->nullable();
            $table->string('Maps_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_de_ventes');
    }
};