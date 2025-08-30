<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_applications', function (Blueprint $table) {
            $table->id();
            $table->string('nom_entreprise');
            $table->string('nom_contact');
            $table->string('telephone')->nullable();
            $table->string('email');
            $table->string('secteur_activite')->nullable();
            $table->longText('message')->nullable(); // Utiliser longText pour un message
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_applications');
    }
};