<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convertir la colonne statut enum en varchar pour plus de flexibilité
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->string('statut_new')->default('en_attente')->after('commentaire');
        });
        
        // Copier les données existantes
        DB::statement("UPDATE demandes_conges SET statut_new = statut::varchar");
        
        // Supprimer l'ancienne colonne et renommer la nouvelle
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
        
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->renameColumn('statut_new', 'statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
        
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->enum('statut', ['en_attente', 'approuve', 'rejete', 'annule'])->default('en_attente')->after('commentaire');
        });
    }
};
