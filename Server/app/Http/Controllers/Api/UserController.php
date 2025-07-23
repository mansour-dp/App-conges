<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'department', 'manager']);

        // Filtres
        if ($request->has('role_id') && $request->role_id) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = $request->input('per_page', 15);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'matricule' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'fonction' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
            'conges_annuels_total' => 'nullable|integer|min:0|max:60',
            'date_embauche' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userData = $validator->validated();
        
        // Mapper les champs vers les vrais noms de colonnes de la base
        $mappedUserData = [
            'name' => $userData['nom'],
            'first_name' => $userData['prenom'],
            'email' => $userData['email'],
            'matricule' => $userData['matricule'],
            'password' => Hash::make($userData['password']),
            'phone' => $userData['telephone'] ?? null,
            'department_id' => $userData['department_id'] ?? null,
            'role_id' => $userData['role_id'],
            'manager_id' => $userData['manager_id'] ?? null,
            'date_embauche' => $userData['date_embauche'] ?? null,
            'conges_annuels_total' => $userData['conges_annuels_total'] ?? 30,
        ];
        
        $mappedUserData['conges_annuels_restants'] = $mappedUserData['conges_annuels_total'];

        $user = User::create($mappedUserData);
        $user->load(['role', 'department', 'manager']);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user,
        ], 201);
    }

    public function show(User $user)
    {
        $user->load(['role', 'department', 'manager', 'subordinates']);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'matricule' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'fonction' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'required|exists:roles,id',
            'manager_id' => 'nullable|exists:users,id',
            'conges_annuels_total' => 'nullable|integer|min:0|max:60',
            'conges_annuels_restants' => 'nullable|integer|min:0',
            'date_embauche' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userData = $validator->validated();

        // Hash password si fourni
        if (!empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }

        $user->update($userData);
        $user->load(['role', 'department', 'manager']);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $user,
        ]);
    }

    public function destroy(User $user)
    {
        // Note: La contrainte ON DELETE CASCADE gère automatiquement la suppression des demandes de congés
        // Pas besoin de vérifier manuellement l'existence des demandes de congés
        
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
        ]);
    }

    public function getManagers()
    {
        $managers = User::with(['role', 'department'])
            ->whereHas('role', function($query) {
                $query->whereIn('name', ['Directeur RH', 'Responsable RH', 'Directeur Unité', 'Superieur']);
            })
            ->where('is_active', true)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->name . ' ' . $user->first_name,
                    'role' => $user->role->nom,
                    'department' => $user->department->name ?? 'N/A',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $managers,
        ]);
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Utilisateur activé' : 'Utilisateur désactivé',
            'data' => $user,
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        // Si des données de mot de passe sont fournies, les valider
        if ($request->has('password')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $newPassword = $request->password;
        } else {
            // Générer un mot de passe temporaire si aucune donnée n'est fournie
            $newPassword = 'temp' . rand(1000, 9999);
        }
        
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $response = [
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès',
        ];

        // Si c'était un mot de passe temporaire, l'inclure dans la réponse
        if (!$request->has('password')) {
            $response['data'] = ['new_password' => $newPassword];
        }

        return response()->json($response);
    }
}
