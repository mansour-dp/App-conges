<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_conges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type_demande', ['conge_annuel', 'conge_maladie', 'conge_maternite', 'conge_paternite', 'conge_sans_solde', 'absence_exceptionnelle', 'report_conge']);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_jours');
            $table->text('motif');
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['en_attente', 'approuve', 'rejete', 'annule'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaire_validation')->nullable();
            $table->json('signatures')->nullable(); // Pour stocker les signatures électroniques
            $table->json('pieces_jointes')->nullable(); // Pour les documents joints
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_conges');
    }
};
