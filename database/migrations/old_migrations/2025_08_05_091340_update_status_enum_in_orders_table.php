<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // On modifie la colonne pour inclure le nouveau statut
            $table->enum('statut', ['Reçue', 'Validée', 'En préparation', 'En cours de livraison', 'Livrée', 'Annulée'])->default('Reçue')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // On revient à l'ancienne définition si on annule la migration
            $table->enum('statut', ['Reçue', 'Validée', 'En préparation', 'Expédiée', 'Livrée', 'Annulée'])->default('Reçue')->change();
        });
    }
};