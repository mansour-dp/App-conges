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
        // Convertir la colonne enum en varchar pour plus de flexibilité
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->string('type_demande_new')->after('user_id');
        });
        
        // Copier les données existantes
        DB::statement("UPDATE demandes_conges SET type_demande_new = type_demande::varchar");
        
        // Supprimer l'ancienne colonne et renommer la nouvelle
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropColumn('type_demande');
        });
        
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->renameColumn('type_demande_new', 'type_demande');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL ne permet pas de supprimer des valeurs d'un enum facilement
        // On peut recréer l'enum sans ces valeurs si nécessaire
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropColumn('type_demande');
        });
        
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->enum('type_demande', [
                'conge_annuel', 
                'conge_maladie', 
                'conge_maternite', 
                'conge_paternite', 
                'conge_sans_solde', 
                'absence_exceptionnelle', 
                'report_conge'
            ])->after('user_id');
        });
    }
};
