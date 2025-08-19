<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            // Vérifier si les colonnes existent avant de les ajouter
            if (!Schema::hasColumn('demandes_conges', 'signature_interresse')) {
                $table->text('signature_interresse')->nullable()->after('signatures');
            }
            if (!Schema::hasColumn('demandes_conges', 'date_validation')) {
                $table->timestamp('date_validation')->nullable()->after('date_soumission');
            }
            if (!Schema::hasColumn('demandes_conges', 'commentaire_validation')) {
                $table->text('commentaire_validation')->nullable()->after('date_validation');
            }
            if (!Schema::hasColumn('demandes_conges', 'valide_par')) {
                $table->unsignedBigInteger('valide_par')->nullable()->after('current_validator');
                $table->foreign('valide_par')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('demandes_conges', function (Blueprint $table) {
            $table->dropForeign(['valide_par']);
            $table->dropColumn(['signature_interresse', 'date_validation', 'commentaire_validation', 'valide_par']);
        });
    }
};
