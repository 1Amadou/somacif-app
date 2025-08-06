<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // On lie la ligne de commande à la variante exacte
            $table->foreignId('unite_de_vente_id')->nullable()->constrained()->after('product_id');
            // On enregistre le nom du calibre pour l'historique
            $table->string('calibre')->nullable()->after('unite');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['unite_de_vente_id']);
            $table->dropColumn(['unite_de_vente_id', 'calibre']);
        });
    }
};