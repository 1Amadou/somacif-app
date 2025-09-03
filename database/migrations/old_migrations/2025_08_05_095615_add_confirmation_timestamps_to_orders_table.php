<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('client_confirmed_at')->nullable()->after('notes');
            $table->timestamp('livreur_confirmed_at')->nullable()->after('client_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['client_confirmed_at', 'livreur_confirmed_at']);
        });
    }
};