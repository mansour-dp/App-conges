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
        Schema::create('demandes_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type_demande')->default('report_conge'); // Type de demande de report
            
            // Données spécifiques au report
            $table->date('date_conge_drh'); // Date congé DRH
            $table->date('date_depart_prevue'); // Date départ prévue
            $table->date('nouvelle_date_debut')->nullable(); // Nouvelle date de début souhaitée
            $table->date('nouvelle_date_fin')->nullable(); // Nouvelle date de fin souhaitée
            $table->integer('duree_jours')->nullable(); // Durée en jours
            $table->text('motif'); // Motif du report
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
            $table->json('pieces_jointes')->nullable(); // Documents joints (bulletin, justificatifs)
            $table->json('form_data')->nullable(); // Données complètes du formulaire
            
            $table->timestamps();
            
            // Clés étrangères
            $table->foreign('current_validator')->references('id')->on('users')->onDelete('set null');
            $table->foreign('valide_par')->references('id')->on('users')->onDelete('set null');
            
            // Index pour performance
            $table->index(['user_id', 'statut']);
            $table->index(['current_validator', 'statut']);
            $table->index(['workflow_step', 'statut']);
            $table->index('date_conge_drh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes_reports');
    }
};
