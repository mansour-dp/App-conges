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
        Schema::create('demandes_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type_absence')->default('absence_exceptionnelle'); // Type d'absence
            
            // Données de la demande
            $table->date('date_matin')->nullable(); // Le matin du
            $table->date('date_apres_midi')->nullable(); // L'après-midi du  
            $table->date('date_journee')->nullable(); // La journée du
            $table->date('periode_debut')->nullable(); // Les journées du
            $table->date('periode_fin')->nullable(); // Jusqu'au
            $table->integer('nb_jours_deductibles')->nullable(); // Nombre de jours déductibles
            $table->text('motif'); // Motif de l'absence
            $table->text('commentaire')->nullable();
            
            // Workflow et validation  
            $table->string('statut')->default('en_attente'); // en_attente, approuve, rejete, annule
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaire_validation')->nullable();
            $table->json('validation_workflow')->nullable(); // Workflow de validation
            $table->unsignedBigInteger('current_validator')->nullable(); // Validateur actuel
            $table->integer('workflow_step')->default(0); // Étape du workflow
            $table->timestamp('date_soumission')->nullable(); // Date de soumission
            
            // Signatures et documents
            $table->json('signatures')->nullable(); // Signatures électroniques
            $table->text('signature_interresse')->nullable(); // Signature de l'intéressé
            $table->json('pieces_jointes')->nullable(); // Documents joints
            $table->json('form_data')->nullable(); // Données complètes du formulaire
            
            $table->timestamps();
            
            // Clés étrangères
            $table->foreign('current_validator')->references('id')->on('users')->onDelete('set null');
            $table->foreign('valide_par')->references('id')->on('users')->onDelete('set null');
            
            // Index pour performance
            $table->index(['user_id', 'statut']);
            $table->index(['current_validator', 'statut']);
            $table->index(['workflow_step', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes_absences');
    }
};
