<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-search-role', function () {
    try {
        $users = \App\Models\User::with(['role', 'department'])
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('nom', 'Directeur Unité');
            })
            ->get();

        return response()->json([
            'success' => true,
            'role_recherche' => 'Directeur Unité',
            'utilisateurs_trouvés' => $users->count(),
            'utilisateurs' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->name,
                    'email' => $user->email,
                    'role' => $user->role ? $user->role->nom : 'Aucun',
                    'is_active' => $user->is_active
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/test-all-roles', function () {
    try {
        $roles = \App\Models\Role::all();
        $result = [];
        
        foreach ($roles as $role) {
            $userCount = \App\Models\User::whereHas('role', function($q) use ($role) {
                $q->where('nom', $role->nom);
            })->where('is_active', true)->count();
            
            $result[] = [
                'role_name' => $role->nom,
                'user_count' => $userCount
            ];
        }

        return response()->json([
            'success' => true,
            'roles_with_users' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
