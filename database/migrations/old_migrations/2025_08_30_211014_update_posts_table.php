<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Renommer les colonnes pour qu'elles correspondent au code Filament
            $table->renameColumn('titre', 'title');
            $table->renameColumn('contenu', 'content');
            $table->renameColumn('date_publication', 'published_at');

            // Remplacer la colonne 'is_published' par 'status'
            $table->dropColumn('is_published');
            $table->string('status')->after('slug')->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Annuler les changements
            $table->renameColumn('title', 'titre');
            $table->renameColumn('content', 'contenu');
            $table->renameColumn('published_at', 'date_publication');

            $table->string('is_published')->after('slug')->default(false); // ou $table->dropColumn('status');
            $table->dropColumn('status');
        });
    }
};