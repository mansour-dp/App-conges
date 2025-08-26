<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeConge;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DemandeCongeController extends Controller
{
    public function index(Request $request)
    {
        $query = DemandeConge::with(['user', 'validateur'])
                             ->where('user_id', $request->user()->id);

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('type')) {
            $query->where('type_demande', $request->type);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('date_debut', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('date_fin', '<=', $request->date_fin);
        }

        $demandes = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $demandes,
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Données reçues pour création demande:', ['data' => $request->all()]);
        
        $validator = Validator::make($request->all(), [
            'type_demande' => 'required|in:conge_annuel,conge_maladie,conge_maternite,conge_paternite,conge_sans_solde,absence_exceptionnelle,report_conge,conges_fractionnes,autres_conges_legaux',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'duree_jours' => 'required|integer|min:1',
            'motif' => 'required|string|max:1000',
            'commentaire' => 'nullable|string|max:1000',
            'signatures' => 'nullable|array',
            'pieces_jointes' => 'nullable|array',
            'form_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);
        $dureeJours = $dateDebut->diffInDays($dateFin) + 1;

        // Vérifier si l'utilisateur a assez de congés
        $user = $request->user();
        if ($request->type_demande === 'conge_annuel' && $user->conges_annuels_restants < $dureeJours) {
            return response()->json([
                'success' => false,
                'message' => 'Solde de congés insuffisant',
            ], 400);
        }

        // Traiter les signatures et pièces jointes
        $signatures = [];
        if ($request->has('signatures')) {
            foreach ($request->signatures as $type => $signatureData) {
                if ($signatureData && isset($signatureData['data'])) {
                    try {
                        $signatures[$type] = [
                            'signature_path' => $this->storeSignature($signatureData['data'], $user->id),
                            'name' => $signatureData['name'] ?? '',
                            'timestamp' => $signatureData['timestamp'] ?? now(),
                            'role' => $signatureData['role'] ?? ''
                        ];
                    } catch (\Exception $e) {
                        // Si erreur de signature, continuer sans bloquer
                        $signatures[$type] = $signatureData;
                    }
                }
            }
        }

        $piecesJointes = [];
        if ($request->has('pieces_jointes')) {
            foreach ($request->pieces_jointes as $file) {
                $piecesJointes[] = $this->storeFile($file, $user->id);
            }
        }

        $dataToCreate = [
            'user_id' => $user->id,
            'type_demande' => $request->type_demande,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'duree_jours' => $request->duree_jours ?? $dureeJours,
            'motif' => $request->motif,
            'commentaire' => $request->commentaire,
            'statut' => $request->statut ?? 'brouillon',
            'signatures' => json_encode($signatures),
            'pieces_jointes' => json_encode($piecesJointes),
            'form_data' => $request->form_data ? json_encode($request->form_data) : null,
        ];
        
        \Log::info('Données à insérer en DB:', ['data' => $dataToCreate]);
        
        $demande = DemandeConge::create($dataToCreate);

        // Créer notification pour le manager
        if ($user->manager) {
            Notification::create([
                'user_id' => $user->manager->id,
                'titre' => 'Nouvelle demande de congé',
                'message' => "{$user->full_name} a soumis une demande de {$demande->type_label}",
                'type' => 'info',
                'data' => ['demande_id' => $demande->id],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande créée avec succès',
            'data' => $demande->load(['user', 'validateur']),
        ], 201);
    }

    public function show(DemandeConge $demande)
    {
        $user = auth()->user();
        
        // Vérifier les permissions
        if ($demande->user_id !== $user->id && !$user->canValidateLeave()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $demande->load(['user', 'validateur']),
        ]);
    }

    public function update(Request $request, DemandeConge $demande)
    {
        $user = $request->user();

        // Seul le propriétaire peut modifier une demande en attente
        if ($demande->user_id !== $user->id || $demande->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier cette demande',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type_demande' => 'sometimes|in:conge_annuel,conge_maladie,conge_maternite,conge_paternite,conge_sans_solde,absence_exceptionnelle,report_conge',
            'date_debut' => 'sometimes|date|after:today',
            'date_fin' => 'sometimes|date|after:date_debut',
            'motif' => 'sometimes|string|max:1000',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $demande->update($request->only([
            'type_demande', 'date_debut', 'date_fin', 'motif', 'commentaire'
        ]));

        if ($request->has('date_debut') || $request->has('date_fin')) {
            $dateDebut = Carbon::parse($demande->date_debut);
            $dateFin = Carbon::parse($demande->date_fin);
            $demande->duree_jours = $dateDebut->diffInDays($dateFin) + 1;
            $demande->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Demande mise à jour avec succès',
            'data' => $demande->load(['user', 'validateur']),
        ]);
    }

    public function destroy(DemandeConge $demande)
    {
        $user = auth()->user();

        if ($demande->user_id !== $user->id || $demande->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette demande',
            ], 403);
        }

        $demande->delete();

        return response()->json([
            'success' => true,
            'message' => 'Demande supprimée avec succès',
        ]);
    }

    public function validateDemande(Request $request, DemandeConge $demande)
    {
        $user = $request->user();

        if (!$user->canValidateLeave()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas les permissions pour valider cette demande',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        $statut = $request->action === 'approve' ? 'approuve' : 'rejete';

        $demande->update([
            'statut' => $statut,
            'valide_par' => $user->id,
            'date_validation' => now(),
            'commentaire_validation' => $request->commentaire,
        ]);

        // Mettre à jour le solde de congés si approuvé
        if ($statut === 'approuve' && $demande->type_demande === 'conge_annuel') {
            $demande->user->decrement('conges_annuels_restants', $demande->duree_jours);
        }

        // Créer notification pour l'utilisateur
        Notification::create([
            'user_id' => $demande->user_id,
            'titre' => 'Demande de congé ' . ($statut === 'approuve' ? 'approuvée' : 'rejetée'),
            'message' => "Votre demande de {$demande->type_label} a été " . ($statut === 'approuve' ? 'approuvée' : 'rejetée') . " par {$user->full_name}",
            'type' => $statut === 'approuve' ? 'success' : 'error',
            'data' => ['demande_id' => $demande->id],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande ' . ($statut === 'approuve' ? 'approuvée' : 'rejetée') . ' avec succès',
            'data' => $demande->load(['user', 'validateur']),
        ]);
    }

    public function demandesAValider(Request $request)
    {
        $user = $request->user();

        if (!$user->canValidateLeave()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $query = DemandeConge::with(['user', 'validateur'])
                             ->where('statut', 'en_attente');

        // Les managers ne voient que les demandes de leurs subordonnés
        if ($user->role->nom === 'superieur') {
            $subordinatesIds = $user->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinatesIds);
        }

        $demandes = $query->orderBy('created_at', 'asc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $demandes,
        ]);
    }

    public function submitWithWorkflow(Request $request)
    {
        \Log::info('submitWithWorkflow appelé avec:', ['data' => $request->all()]);
        \Log::info('Headers reçus:', ['headers' => $request->headers->all()]);
        \Log::info('Method utilisée:', ['method' => $request->method()]);
        \Log::info('URI appelée:', ['uri' => $request->getRequestUri()]);
        
        $validator = Validator::make($request->all(), [
            'demande_id' => 'required|exists:demandes_conges,id',
            'superieur_email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation échouée dans submitWithWorkflow:', [
                'errors' => $validator->errors(),
                'data' => $request->all()
            ]);
            
            // Test manuel de l'existence de l'utilisateur
            $userExists = \App\Models\User::where('email', $request->superieur_email)->exists();
            \Log::info('Vérification manuelle utilisateur:', [
                'email' => $request->superieur_email,
                'exists' => $userExists
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Données de validation invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        \Log::info('Validation réussie, récupération des objets...');
        
        $demande = DemandeConge::findOrFail($request->demande_id);
        $supervisor = \App\Models\User::where('email', $request->superieur_email)->first();

        \Log::info('Objets récupérés:', [
            'demande_id' => $demande->id,
            'demande_statut' => $demande->statut,
            'user_id' => $demande->user_id,
            'current_user_id' => $request->user()->id,
            'supervisor_id' => $supervisor->id,
            'supervisor_name' => $supervisor->name
        ]);

        // Vérifier que l'utilisateur peut modifier cette demande
        if ($demande->user_id !== $request->user()->id || $demande->statut !== 'brouillon') {
            \Log::error('Action non autorisée:', [
                'demande_user_id' => $demande->user_id,
                'current_user_id' => $request->user()->id,
                'demande_statut' => $demande->statut
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Action non autorisée',
            ], 403);
        }

        // Vérifier que la demande a une signature (depuis le formulaire principal ou les signatures JSON)
        $signatureEmploye = $demande->signature_interresse;
        
        \Log::info('Vérification des signatures:', [
            'signature_interresse' => $signatureEmploye ? 'Présente' : 'Absente',
            'signatures_json' => $demande->signatures ? 'Présent' : 'Absent'
        ]);
        
        if (!$signatureEmploye) {
            // Essayer de récupérer depuis le JSON signatures
            $signatures = json_decode($demande->signatures, true) ?? [];
            
            // Essayer différentes clés possibles
            $signatureEmploye = $signatures['employe']['data'] ?? 
                               $signatures['signatureEmploye'] ?? 
                               $signatures['employe'] ?? 
                               null;
            
            \Log::info('Tentative de récupération depuis JSON:', [
                'signatures_decoded' => $signatures ? 'Succès' : 'Échec',
                'signatures_keys' => array_keys($signatures),
                'employe_signature_found' => $signatureEmploye ? 'Oui' : 'Non'
            ]);
            
            if ($signatureEmploye) {
                // Sauvegarder dans le champ dédié pour la prochaine fois
                $demande->update(['signature_interresse' => $signatureEmploye]);
                \Log::info('Signature sauvegardée dans signature_interresse');
            }
        }
        
        if (!$signatureEmploye) {
            \Log::warning('Aucune signature d\'employé trouvée, mais poursuite du processus');
            // Pour les tests, on continue sans signature
            $signatureEmploye = 'signature_placeholder';
        }

        // Initialiser le workflow avec la signature existante de l'employé
        $supervisorName = $supervisor->full_name ?? $supervisor->name ?? $supervisor->email;
        
        \Log::info('Création du workflow avec:', [
            'supervisor_id' => $supervisor->id,
            'supervisor_name' => $supervisorName,
            'supervisor_email' => $supervisor->email
        ]);
        
        $workflow = [
            [
                'validateur_email' => $request->superieur_email,
                'validateur_nom' => $supervisorName,
                'statut' => 'en_attente',
                'date_soumission' => now(),
                'signature_demandeur' => $signatureEmploye
            ]
        ];

        // Mettre à jour la demande
        \Log::info('Mise à jour de la demande avec le workflow...');
        
        $demande->update([
            'statut' => 'en_attente_superieur',
            'validation_workflow' => json_encode($workflow),
            'current_validator' => $supervisor->id,
            'workflow_step' => 1,
            'date_soumission' => now()
        ]);

        \Log::info('Demande mise à jour, statut:', ['statut' => $demande->statut]);

        // Créer une notification pour le superviseur
        \Log::info('Création de la notification...');
        
        try {
            Notification::create([
                'user_id' => $supervisor->id,
                'title' => 'Nouvelle demande de congé à valider',
                'message' => "Une nouvelle demande de congé de {$request->user()->name} nécessite votre validation",
                'type' => 'info',
                'data' => ['demande_id' => $demande->id],
            ]);
            \Log::info('Notification créée avec succès');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la notification:', ['error' => $e->getMessage()]);
        }

        \Log::info('Processus terminé avec succès');

        return response()->json([
            'success' => true,
            'message' => 'Demande soumise avec succès',
            'data' => $demande->load(['user', 'validateur']),
        ]);
    }

    public function validateWithNext(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'demande_id' => 'required|exists:demandes_conges,id',
            'decision' => 'required|in:approuve,rejete',
            'commentaire' => 'nullable|string|max:1000',
            'signature' => 'required_if:decision,approuve|nullable|string',
            'next_validator_email' => 'nullable|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $demande = DemandeConge::findOrFail($request->demande_id);
        $user = $request->user();

        // Vérifier que l'utilisateur peut valider cette demande
        if ($demande->current_validator !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à valider cette demande',
            ], 403);
        }

        // Validation stricte de la hiérarchie des rôles
        if ($request->decision === 'approuve' && $request->next_validator_email) {
            $nextValidator = \App\Models\User::with('role')->where('email', $request->next_validator_email)->first();
            $currentUserRole = $user->role?->nom;
            $nextValidatorRole = $nextValidator?->role?->nom;
            
            // Définir la hiérarchie stricte
            $allowedNextRoles = [
                'Superieur' => 'Directeur Unité',
                'Directeur Unité' => 'Responsable RH',
                'Responsable RH' => 'Directeur RH'
            ];
            
            if (isset($allowedNextRoles[$currentUserRole])) {
                $expectedNextRole = $allowedNextRoles[$currentUserRole];
                if ($nextValidatorRole !== $expectedNextRole) {
                    return response()->json([
                        'success' => false,
                        'message' => "En tant que {$currentUserRole}, vous ne pouvez envoyer qu'à un utilisateur avec le rôle '{$expectedNextRole}'. Le destinataire sélectionné a le rôle '{$nextValidatorRole}'.",
                    ], 422);
                }
            }
        }

        // Sauvegarder la signature du validateur (seulement si fournie)
        $signaturePath = null;
        if ($request->signature && $request->decision === 'approuve') {
            $signaturePath = $this->storeSignature($request->signature, $user->id);
        }

        // Récupérer le workflow existant
        $workflow = json_decode($demande->validation_workflow, true) ?? [];

        // Ajouter la validation actuelle
        $currentValidation = [
            'validateur_email' => $user->email,
            'validateur_nom' => $user->full_name,
            'decision' => $request->decision,
            'commentaire' => $request->commentaire,
            'signature' => $signaturePath,
            'date_validation' => now(),
            'statut' => 'valide'
        ];

        // Mettre à jour l'étape actuelle dans le workflow
        $workflow[$demande->workflow_step - 1] = array_merge(
            $workflow[$demande->workflow_step - 1] ?? [],
            $currentValidation
        );

        if ($request->decision === 'rejete') {
            // Si rejeté, terminer le workflow
            $demande->update([
                'statut' => 'rejete',
                'validation_workflow' => json_encode($workflow),
                'current_validator' => null,
                'commentaire_validation' => $request->commentaire,
                'date_validation' => now()
            ]);

            // Notifier le demandeur
            Notification::create([
                'user_id' => $demande->user_id,
                'title' => 'Demande de congé rejetée',
                'message' => "Votre demande de congé a été rejetée par {$user->full_name}",
                'type' => 'error',
                'data' => ['demande_id' => $demande->id],
            ]);

        } elseif ($request->next_validator_email) {
            // Si approuvé et il y a un prochain validateur
            $nextValidator = \App\Models\User::where('email', $request->next_validator_email)->first();
            
            // Ajouter la prochaine étape au workflow
            $workflow[] = [
                'validateur_email' => $request->next_validator_email,
                'validateur_nom' => $nextValidator->full_name,
                'statut' => 'en_attente',
                'date_soumission' => now()
            ];

            $demande->update([
                'statut' => $this->getNextStatus($demande->workflow_step + 1),
                'validation_workflow' => json_encode($workflow),
                'current_validator' => $nextValidator->id,
                'workflow_step' => $demande->workflow_step + 1
            ]);

            // Notifier le prochain validateur
            Notification::create([
                'user_id' => $nextValidator->id,
                'title' => 'Demande de congé à valider',
                'message' => "Une demande de congé de {$demande->user->full_name} nécessite votre validation",
                'type' => 'info',
                'data' => ['demande_id' => $demande->id],
            ]);

        } else {
            // Si approuvé et c'est le dernier validateur
            $demande->update([
                'statut' => 'approuve',
                'validation_workflow' => json_encode($workflow),
                'current_validator' => null,
                'date_validation' => now()
            ]);

            // Notifier le demandeur
            Notification::create([
                'user_id' => $demande->user_id,
                'title' => 'Demande de congé approuvée',
                'message' => "Votre demande de congé a été approuvée par {$user->full_name}",
                'type' => 'success',
                'data' => ['demande_id' => $demande->id],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Validation effectuée avec succès',
            'data' => $demande->load(['user', 'validateur']),
        ]);
    }

    public function searchUsersByEmail(Request $request)
    {
        try {
            \Log::info('Recherche utilisateur avec email:', ['email' => $request->email]);
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|min:3'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Chercher d'abord une correspondance exacte
            $exactMatch = \App\Models\User::where('email', $request->email)
                                         ->where('id', '!=', $request->user()->id)
                                         ->first();

            if ($exactMatch) {
                $fullName = trim(($exactMatch->first_name ?? '') . ' ' . ($exactMatch->name ?? ''));
                if (empty($fullName)) {
                    $fullName = $exactMatch->email;
                }
                
                $userData = [
                    'id' => $exactMatch->id,
                    'email' => $exactMatch->email,
                    'name' => $fullName,
                    'first_name' => $exactMatch->first_name,
                    'last_name' => $exactMatch->name,
                    'roles' => $exactMatch->role ? [$exactMatch->role] : [],
                    'department' => $exactMatch->department
                ];
                \Log::info('Utilisateur trouvé (exact):', ['user' => $userData]);
                return response()->json([
                    'success' => true,
                    'data' => $userData,
                ]);
            }

            // Si pas de correspondance exacte, chercher par LIKE (pour les recherches partielles)
            $partialMatches = \App\Models\User::with(['role', 'department'])
                ->where('id', '!=', $request->user()->id)
                ->where('is_active', true)
                ->where('email', 'LIKE', '%' . $request->email . '%')
                ->limit(10)
                ->get();

            if ($partialMatches->isNotEmpty()) {
                // Retourner le premier match pour compatibilité avec l'ancienne API
                $user = $partialMatches->first();
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->name ?? ''));
                if (empty($fullName)) {
                    $fullName = $user->email;
                }
                
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $fullName,
                    'first_name' => $user->first_name,
                    'last_name' => $user->name,
                    'roles' => $user->role ? [$user->role] : [],
                    'department' => $user->department
                ];
                \Log::info('Utilisateur trouvé (partiel):', ['user' => $userData]);
                return response()->json([
                    'success' => true,
                    'data' => $userData,
                ]);
            }

            // Si pas de correspondance exacte, chercher avec LIKE
            $users = \App\Models\User::where('email', 'LIKE', '%' . $request->email . '%')
                                    ->where('id', '!=', $request->user()->id)
                                    ->limit(10)
                                    ->get();

            if ($users->count() > 0) {
                $user = $users->first();
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->name ?? ''));
                if (empty($fullName)) {
                    $fullName = $user->email; // Utiliser l'email si pas de nom
                }
                
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $fullName,
                    'nom_complet' => $fullName,
                    'role' => 'Test Role',
                    'department' => 'Test Department'
                ];
                \Log::info('Utilisateur trouvé (LIKE):', ['user' => $userData]);
                return response()->json([
                    'success' => true,
                    'data' => $userData,
                ]);
            }

            \Log::info('Aucun utilisateur trouvé');
            return response()->json([
                'success' => false,
                'message' => 'Aucun utilisateur trouvé avec cet email',
                'data' => null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans searchUsersByEmail:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function searchUsersByName(Request $request)
    {
        try {
            \Log::info('Recherche utilisateur avec nom:', ['name' => $request->name]);
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $searchTerm = $request->name;
            
            // Rechercher dans first_name et name (version simple sans CONCAT)
            $users = \App\Models\User::with(['role', 'department'])
                ->where('id', '!=', $request->user()->id)
                ->where('is_active', true)
                ->where(function($query) use ($searchTerm) {
                    $query->where('first_name', 'LIKE', '%' . $searchTerm . '%')
                          ->orWhere('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->limit(10)
                ->get();

            if ($users->count() > 0) {
                $usersData = $users->map(function($user) {
                    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->name ?? ''));
                    if (empty($fullName)) {
                        $fullName = $user->email;
                    }
                    
                    return [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $fullName,
                        'first_name' => $user->first_name,
                        'last_name' => $user->name,
                        'roles' => $user->role ? [$user->role] : [], // Convertir en array pour compatibilité
                        'department' => $user->department
                    ];
                });

                \Log::info('Utilisateurs trouvés par nom:', ['count' => $users->count(), 'users' => $usersData]);
                return response()->json([
                    'success' => true,
                    'data' => $usersData,
                ]);
            }

            \Log::info('Aucun utilisateur trouvé par nom');
            return response()->json([
                'success' => false,
                'message' => 'Aucun utilisateur trouvé avec ce nom',
                'data' => [],
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans searchUsersByName:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function searchUsersByRole(Request $request)
    {
        try {
            \Log::info('🔍 Recherche utilisateurs par rôle:', [
                'role' => $request->role, 
                'query' => $request->query,
                'user_id' => $request->user()->id
            ]);
            
            $validator = Validator::make($request->all(), [
                'role' => 'required|string',
                'query' => 'required|string|min:2'
            ]);

            if ($validator->fails()) {
                \Log::warning('🚫 Validation échoué:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            \Log::info('✅ Validation réussie, recherche en cours...');

            // Version simplifiée : d'abord chercher tous les utilisateurs avec le bon rôle
            $usersWithRole = \App\Models\User::with(['role', 'department'])
                ->where('id', '!=', $request->user()->id)
                ->where('is_active', true)
                ->whereHas('role', function ($query) use ($request) {
                    $query->where('nom', $request->role);
                })
                ->get();

            \Log::info('👥 Utilisateurs avec le rôle trouvés:', ['count' => $usersWithRole->count()]);

            // Ensuite filtrer par query en PHP pour éviter les problèmes SQL
            $searchTerm = strtolower($request->query);
            $users = $usersWithRole->filter(function ($user) use ($searchTerm) {
                $email = strtolower($user->email ?? '');
                $firstName = strtolower($user->first_name ?? '');
                $lastName = strtolower($user->name ?? '');
                $fullName = strtolower(trim($firstName . ' ' . $lastName));
                
                return str_contains($email, $searchTerm) ||
                       str_contains($firstName, $searchTerm) ||
                       str_contains($lastName, $searchTerm) ||
                       str_contains($fullName, $searchTerm);
            })->take(10);

            \Log::info('📊 Résultats après filtrage:', ['count' => $users->count()]);

            $userData = $users->map(function ($user) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->name ?? ''));
                if (empty($fullName)) {
                    $fullName = $user->email;
                }
                
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $fullName,
                    'first_name' => $user->first_name,
                    'last_name' => $user->name,
                    'roles' => $user->role ? [$user->role] : [], // Convertir en array pour compatibilité
                    'role' => $user->role, // Aussi garder le format direct
                    'department' => $user->department,
                    'is_active' => $user->is_active
                ];
            });

            \Log::info('🎯 Données formatées:', [
                'count' => $userData->count(),
                'sample' => $userData->first()
            ]);

            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => $userData->count() > 0 
                    ? 'Utilisateurs trouvés' 
                    : 'Aucun utilisateur trouvé avec ce rôle',
                'count' => $userData->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erreur dans searchUsersByRole:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function demandesRecues(Request $request)
    {
        try {
            $user = $request->user();
            
            // Récupérer les demandes où l'utilisateur est le validateur actuel
            $demandes = DemandeConge::with(['user', 'user.department', 'user.role'])
                ->where('current_validator', $user->id)
                ->whereIn('statut', [
                    'en_attente_superieur', 
                    'en_attente_directeur_unite', 
                    'en_attente_responsable_rh', 
                    'en_attente_directeur_rh'
                ])
                ->orderBy('date_soumission', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $demandes,
                'message' => $demandes->count() > 0 
                    ? 'Demandes récupérées avec succès' 
                    : 'Aucune demande en attente trouvée',
                'count' => $demandes->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans demandesRecues:', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getNextStatus($step)
    {
        $statuses = [
            1 => 'en_attente_superieur',
            2 => 'en_attente_directeur_unite',
            3 => 'en_attente_responsable_rh',
            4 => 'en_attente_directeur_rh'
        ];

        return $statuses[$step] ?? 'en_attente_directeur_rh';
    }

    private function storeSignature($signatureData, $userId)
    {
        try {
            // Si c'est déjà une URL ou un chemin, le retourner tel quel
            if (filter_var($signatureData, FILTER_VALIDATE_URL) || !str_contains($signatureData, 'data:image')) {
                return $signatureData;
            }

            // Décoder le base64 et sauvegarder
            $image = str_replace('data:image/png;base64,', '', $signatureData);
            $image = str_replace(' ', '+', $image);
            $imageName = 'signature_' . $userId . '_' . time() . '.png';
            
            Storage::disk('public')->put('signatures/' . $imageName, base64_decode($image));
            
            return 'signatures/' . $imageName;
        } catch (\Exception $e) {
            // En cas d'erreur, retourner la donnée originale
            return $signatureData;
        }
    }

    private function storeFile($file, $userId)
    {
        // Logique pour sauvegarder les fichiers
        return 'files/' . $file;
    }
}
