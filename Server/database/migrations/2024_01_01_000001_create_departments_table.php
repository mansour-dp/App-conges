<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('code')->unique();
            // $table->decimal('budget', 15, 2)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->timestamps();
            
            // Note: La contrainte étrangère sera ajoutée après la création de la table users
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
