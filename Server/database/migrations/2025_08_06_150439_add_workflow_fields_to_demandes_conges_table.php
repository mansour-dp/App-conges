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
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->json('validation_workflow')->nullable()->after('commentaire_validation');
            $table->unsignedBigInteger('current_validator')->nullable()->after('validation_workflow');
            $table->integer('workflow_step')->default(0)->after('current_validator');
            $table->timestamp('date_soumission')->nullable()->after('workflow_step');
            
            $table->foreign('current_validator')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropForeign(['current_validator']);
            $table->dropColumn(['validation_workflow', 'current_validator', 'workflow_step', 'date_soumission']);
        });
    }
};
