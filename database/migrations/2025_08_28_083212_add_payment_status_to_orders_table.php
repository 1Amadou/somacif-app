<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('statut_paiement')->default('Non réglé')->after('statut');
            $table->decimal('montant_paye', 10, 2)->default(0)->after('montant_total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['statut_paiement', 'montant_paye']);
        });
    }
};