<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('contract_path')->nullable()->after('entrepots_de_livraison');
            $table->timestamp('terms_accepted_at')->nullable()->after('contract_path');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['contract_path', 'terms_accepted_at']);
        });
    }
};