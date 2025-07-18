<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Index pour les clés étrangères (performance des jointures)
            $table->index('role_id', 'idx_users_role_id');
            $table->index('department_id', 'idx_users_department_id'); 
            $table->index('manager_id', 'idx_users_manager_id');
            
            // Index pour les filtres courants
            $table->index('is_active', 'idx_users_is_active');
            $table->index(['role_id', 'is_active'], 'idx_users_role_active');
            $table->index(['department_id', 'is_active'], 'idx_users_dept_active');
            
            // Index composite pour la recherche de managers
            $table->index(['role_id', 'is_active', 'department_id'], 'idx_users_manager_search');
            
            // Index de recherche full-text pour la barre de recherche
            $table->index(['first_name', 'name'], 'idx_users_fullname');
            $table->index('email', 'idx_users_email');
            $table->index('matricule', 'idx_users_matricule');
            
            // Index pour le tri par date de création
            $table->index('created_at', 'idx_users_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_id');
            $table->dropIndex('idx_users_department_id');
            $table->dropIndex('idx_users_manager_id');
            $table->dropIndex('idx_users_is_active');
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_dept_active');
            $table->dropIndex('idx_users_manager_search');
            $table->dropIndex('idx_users_fullname');
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_matricule');
            $table->dropIndex('idx_users_created_at');
        });
    }
};
