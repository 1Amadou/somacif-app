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
        Schema::create('arrivages', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_arrivage')->useCurrent();
            $table->string('fournisseur')->nullable();
            $table->string('numero_bon_livraison')->nullable()->unique();
            $table->json('details_produits');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->comment('Utilisateur qui a enregistré l\'arrivage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arrivages');
    }
};