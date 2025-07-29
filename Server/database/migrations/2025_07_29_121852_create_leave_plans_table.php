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
        Schema::create('leave_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la période
            $table->date('start_date'); // Date de début
            $table->date('end_date'); // Date de fin
            $table->integer('days_count'); // Nombre de jours
            $table->enum('leave_type', [
                'conge_annuel', 
                'conges_fractionnes', 
                'autres_conges_legaux', 
                'conge_maladie', 
                'conge_maternite', 
                'conge_paternite', 
                'conge_sans_solde', 
                'absence_exceptionnelle', 
                'report_conge'
            ])->default('conge_annuel'); // Type de congé
            $table->text('description')->nullable(); // Description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_plans');
    }
};
