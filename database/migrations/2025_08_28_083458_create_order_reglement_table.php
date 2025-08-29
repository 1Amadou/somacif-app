<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_reglement', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reglement_id')->constrained()->cascadeOnDelete();
            $table->primary(['order_id', 'reglement_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('order_reglement');
    }
};