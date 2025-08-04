<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('point_de_vente_id')->constrained('point_de_ventes')->onDelete('cascade');
            $table->integer('quantite_stock');
            $table->timestamps();
            $table->unique(['product_id', 'point_de_vente_id']); // Un produit ne peut avoir qu'une seule entrée de stock par point de vente
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory');
    }
};