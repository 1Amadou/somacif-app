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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('type', ['Grossiste', 'Hôtel/Restaurant', 'Particulier']);
            $table->string('telephone')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('identifiant_unique_somacif')->unique();
            $table->text('entrepots_de_livraison')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};