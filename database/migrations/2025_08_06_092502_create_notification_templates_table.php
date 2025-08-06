<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nom lisible pour l\'admin');
            $table->string('key')->unique()->comment('Clé programmatique');
            $table->enum('channel', ['mail', 'sms']);
            $table->boolean('is_active')->default(true);
            $table->string('subject')->nullable()->comment('Sujet pour les emails');
            $table->text('body')->comment('Contenu du message avec variables');
            $table->text('description')->nullable()->comment('Description pour l\'admin');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};