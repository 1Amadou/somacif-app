<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('date_reglement');
            $table->decimal('montant_verse', 10, 2)->comment('Le montant total que le client a physiquement donné');
            $table->decimal('montant_calcule', 10, 2)->comment('Le montant total calculé à partir des ventes déclarées');
            $table->string('methode_paiement')->default('Espèces');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglements');
    }
};