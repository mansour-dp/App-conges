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
        Schema::table('demandes_absences', function (Blueprint $table) {
            $table->json('form_data')->nullable()->after('signature_interresse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_absences', function (Blueprint $table) {
            $table->dropColumn('form_data');
        });
    }
};
