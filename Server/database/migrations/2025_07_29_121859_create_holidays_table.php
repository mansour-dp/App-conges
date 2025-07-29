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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du jour férié
            $table->date('date'); // Date du jour férié
            $table->enum('type', ['national', 'religious', 'local', 'company'])->default('national'); // Type de jour férié
            $table->text('description')->nullable(); // Description
            $table->boolean('is_recurring')->default(false); // Si c'est récurrent chaque année
            $table->boolean('is_active')->default(true); // Statut actif/inactif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
