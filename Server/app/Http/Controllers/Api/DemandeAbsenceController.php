<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeAbsence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemandeAbsenceController extends Controller
{
    /**
     * Liste des demandes d'absence avec filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DemandeAbsence::with(['user.role', 'user.department', 'currentValidator.role']);

            // Filtres
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('current_validator')) {
                $query->where('current_validator', $request->current_validator);
            }

            $demandes = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $demandes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes d\'absence',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Demandes en attente de validation pour l'utilisateur connecté
     */
    public function demandesEnAttente(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $demandes = DemandeAbsence::with(['user.role', 'user.department'])
                ->where('current_validator', $user->id)
                ->where('statut', 'en_attente')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $demandes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes en attente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle demande d'absence
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type_absence' => 'required|string',
            'motif' => 'required|string',
            'date_matin' => 'nullable|date',
            'date_apres_midi' => 'nullable|date', 
            'date_journee' => 'nullable|date',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date|after_or_equal:periode_debut',
            'nb_jours_deductibles' => 'nullable|integer|min:0',
            'signature_interresse' => 'required|string',
            'form_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            
            // Créer la demande
            $demande = DemandeAbsence::create([
                'user_id' => $user->id,
                'type_absence' => $request->type_absence,
                'date_matin' => $request->date_matin,
                'date_apres_midi' => $request->date_apres_midi,
                'date_journee' => $request->date_journee,
                'periode_debut' => $request->periode_debut,
                'periode_fin' => $request->periode_fin,
                'nb_jours_deductibles' => $request->nb_jours_deductibles,
                'motif' => $request->motif,
                'commentaire' => $request->commentaire,
                'signature_interresse' => $request->signature_interresse,
                'form_data' => $request->form_data,
                'statut' => 'en_attente',
                'workflow_step' => 0,
                'date_soumission' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $demande->load(['user.role', 'user.department']),
                'message' => 'Demande d\'absence créée avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soumettre une demande avec workflow hiérarchique
     */
    public function submitWithWorkflow(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'demande_id' => 'required|exists:demandes_absences,id',
            'superieur_email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $demande = DemandeAbsence::findOrFail($request->demande_id);
            $user = $request->user();
            $superieur = User::where('email', $request->superieur_email)->first();

            // Initialiser le workflow
            $workflow = [
                [
                    'etape' => 1,
                    'role_requis' => 'Superieur',
                    'validateur_email' => $superieur->email,
                    'validateur_nom' => $superieur->full_name,
                    'statut' => 'en_attente',
                    'date_assignation' => now()
                ],
                [
                    'etape' => 2,
                    'role_requis' => 'Directeur Unité',
                    'statut' => 'en_attente'
                ],
                [
                    'etape' => 3,
                    'role_requis' => 'Responsable RH',
                    'statut' => 'en_attente'
                ],
                [
                    'etape' => 4,
                    'role_requis' => 'Directeur RH',
                    'statut' => 'en_attente'
                ]
            ];

            $demande->update([
                'validation_workflow' => $workflow,
                'current_validator' => $superieur->id,
                'workflow_step' => 1,
                'statut' => 'en_attente_superieur'
            ]);

            \Log::info('Demande absence soumise au workflow', [
                'demande_id' => $demande->id,
                'user_id' => $user->id,
                'superieur_id' => $superieur->id,
                'superieur_email' => $superieur->email,
                'nouveau_statut' => 'en_attente_superieur'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande soumise avec succès au workflow de validation',
                'data' => $demande->fresh(['user', 'currentValidator'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider une demande avec passage au validateur suivant
     */
    public function validateWithNext(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'demande_id' => 'required|exists:demandes_absences,id',
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

        $demande = DemandeAbsence::findOrFail($request->demande_id);
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
            $nextValidator = User::with('role')->where('email', $request->next_validator_email)->first();
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

        // Récupérer le workflow existant (déjà un array grâce au cast)
        $workflow = $demande->validation_workflow ?? [];

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
            // Rejeter la demande
            $demande->update([
                'statut' => 'rejete',
                'valide_par' => $user->id,
                'date_validation' => now(),
                'commentaire_validation' => $request->commentaire,
                'validation_workflow' => $workflow,
                'current_validator' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande rejetée avec succès',
                'data' => $demande->fresh(['user', 'validateur'])
            ]);
        }

        // Si approuvé, passer au validateur suivant ou finaliser
        if ($request->next_validator_email) {
            $nextValidator = User::where('email', $request->next_validator_email)->first();
            
            // Mettre à jour l'étape suivante du workflow
            if (isset($workflow[$demande->workflow_step])) {
                $workflow[$demande->workflow_step] = array_merge(
                    $workflow[$demande->workflow_step],
                    [
                        'validateur_email' => $nextValidator->email,
                        'validateur_nom' => $nextValidator->full_name,
                        'date_assignation' => now()
                    ]
                );
            }

            $demande->update([
                'validation_workflow' => $workflow,
                'current_validator' => $nextValidator->id,
                'workflow_step' => $demande->workflow_step + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande approuvée et transmise au validateur suivant',
                'data' => $demande->fresh(['user', 'currentValidator'])
            ]);
        } else {
            // Validation finale
            $demande->update([
                'statut' => 'approuve',
                'valide_par' => $user->id,
                'date_validation' => now(),
                'commentaire_validation' => $request->commentaire,
                'validation_workflow' => $workflow,
                'current_validator' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande approuvée définitivement',
                'data' => $demande->fresh(['user', 'validateur'])
            ]);
        }
    }

    /**
     * Obtenir une demande spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $demande = DemandeAbsence::with(['user.role', 'user.department', 'currentValidator.role', 'validateur.role'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $demande
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée'
            ], 404);
        }
    }

    /**
     * Récupérer les demandes d'absence reçues par l'utilisateur connecté pour validation
     */
    public function demandesRecues(Request $request): JsonResponse
    {
        try {
            \Log::info('🔍 demandesRecues appelé pour les absences');
            
            $currentUser = auth()->user();
            
            if (!$currentUser) {
                \Log::error('❌ Utilisateur non authentifié');
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            \Log::info('👤 Utilisateur authentifié:', [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email
            ]);

            // Récupérer les demandes où l'utilisateur connecté est le validateur actuel
            $query = DemandeAbsence::with(['user', 'currentValidator'])
            ->where('current_validator', $currentUser->id)
            ->whereIn('statut', [
                'en_attente_superieur',
                'en_attente_directeur_unite', 
                'en_attente_responsable_rh',
                'en_attente_directeur_rh'
            ])
            ->orderBy('created_at', 'desc');

            $demandes = $query->get();
            
            \Log::info('📋 Demandes trouvées:', [
                'count' => $demandes->count(),
                'demandes_ids' => $demandes->pluck('id')->toArray()
            ]);

            // Formater les données pour le frontend de manière sécurisée
            $formattedDemandes = $demandes->map(function ($demande) {
                try {
                    return [
                        'id' => $demande->id,
                        'user' => [
                            'id' => $demande->user?->id,
                            'name' => $demande->user?->name ?? 'N/A',
                            'first_name' => $demande->user?->first_name ?? 'N/A',
                            'matricule' => $demande->user?->matricule ?? 'N/A',
                        ],
                        'type_demande' => 'absence',
                        'type_absence' => $demande->type_absence ?? 'personnelle',
                        'motif' => $demande->motif,
                        'statut' => $demande->statut,
                        'date_soumission' => $demande->date_soumission ?? $demande->created_at,
                        'created_at' => $demande->created_at,
                        'current_validator' => $demande->current_validator,
                        'workflow_step' => $demande->workflow_step ?? 0,
                    ];
                } catch (\Exception $e) {
                    \Log::error('❌ Erreur lors du formatage d\'une demande:', [
                        'demande_id' => $demande->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })->filter(); // Supprimer les valeurs null

            \Log::info('✅ Demandes formatées:', [
                'count' => $formattedDemandes->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demandes d\'absence récupérées avec succès',
                'data' => $formattedDemandes,
                'total' => $formattedDemandes->count(),
                'user_role' => $currentUser->role?->nom ?? $currentUser->role?->name ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Erreur dans demandesRecues:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes d\'absence',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stocker une signature électronique
     */
    private function storeSignature($signatureData, $userId): string
    {
        try {
            // Décoder l'image base64
            $data = explode(',', $signatureData);
            $imageData = base64_decode($data[1]);
            
            // Générer un nom de fichier unique
            $fileName = 'signature_absence_' . $userId . '_' . Str::uuid() . '.png';
            $filePath = 'signatures/absences/' . $fileName;
            
            // Sauvegarder le fichier
            Storage::disk('public')->put($filePath, $imageData);
            
            return $filePath;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la sauvegarde de la signature: ' . $e->getMessage());
        }
    }
}
