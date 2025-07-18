<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Récupérer tous les rôles
     */
    public function index(): JsonResponse
    {
        try {
            $roles = Role::orderBy('nom')->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Rôles récupérés avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rôles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau rôle
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:roles,nom',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
            ]);

            $role = Role::create($validated);

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Rôle créé avec succès'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un rôle spécifique
     */
    public function show(Role $role): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Rôle récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:roles,nom,' . $role->id,
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
            ]);

            $role->update($validated);

            return response()->json([
                'success' => true,
                'data' => $role->fresh(),
                'message' => 'Rôle mis à jour avec succès'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // Vérifier si le rôle est utilisé par des utilisateurs
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce rôle car il est utilisé par des utilisateurs'
                ], 400);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rôle supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
