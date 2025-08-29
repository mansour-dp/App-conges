<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemandeReportController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Liste des demandes de report avec filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DemandeReport::with(['user.role', 'user.department', 'currentValidator.role']);

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
                'message' => 'Erreur lors de la récupération des demandes de report',
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
            
            $demandes = DemandeReport::with(['user.role', 'user.department'])
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
     * Créer une nouvelle demande de report
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type_demande' => 'required|string',
            'date_conge_drh' => 'required|date',
            'date_depart_prevue' => 'required|date',
            'nouvelle_date_debut' => 'nullable|date',
            'nouvelle_date_fin' => 'nullable|date|after_or_equal:nouvelle_date_debut',
            'motif' => 'required|string',
            'signature_interresse' => 'required|string',
            'pieces_jointes' => 'nullable|array',
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
            $demande = DemandeReport::create([
                'user_id' => $user->id,
                'type_demande' => $request->type_demande,
                'date_conge_drh' => $request->date_conge_drh,
                'date_depart_prevue' => $request->date_depart_prevue,
                'nouvelle_date_debut' => $request->nouvelle_date_debut,
                'nouvelle_date_fin' => $request->nouvelle_date_fin,
                'motif' => $request->motif,
                'commentaire' => $request->commentaire,
                'signature_interresse' => $request->signature_interresse,
                'pieces_jointes' => $request->pieces_jointes,
                'form_data' => $request->form_data,
                'statut' => 'en_attente',
                'workflow_step' => 0,
                'date_soumission' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $demande->load(['user.role', 'user.department']),
                'message' => 'Demande de report créée avec succès'
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
            'demande_id' => 'required|exists:demandes_reports,id',
            'superieur_email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $demande = DemandeReport::findOrFail($request->demande_id);
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
            'demande_id' => 'required|exists:demandes_reports,id',
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

        $demande = DemandeReport::findOrFail($request->demande_id);
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
     * Récupérer les demandes de report reçues pour validation par l'utilisateur connecté
     */
    public function demandesRecues(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Récupérer les demandes où l'utilisateur est le validateur actuel
            $demandes = DemandeReport::with(['user', 'user.department', 'user.role'])
                ->where('current_validator', $user->id)
                ->whereIn('statut', [
                    'en_attente_superieur', 
                    'en_attente_directeur_unite', 
                    'en_attente_responsable_rh', 
                    'en_attente_directeur_rh'
                ])
                ->orderBy('date_soumission', 'desc')
                ->get();

            // Transformation des données pour la compatibilité frontend
            $demandesFormatted = $demandes->map(function ($demande) {
                return [
                    'id' => $demande->id,
                    'user_id' => $demande->user_id,
                    'type_demande' => $demande->type_demande,
                    'statut' => $demande->statut,
                    'date_conge_drh' => $demande->date_conge_drh,
                    'date_depart_prevue' => $demande->date_depart_prevue,
                    'nouvelle_date_debut' => $demande->nouvelle_date_debut,
                    'nouvelle_date_fin' => $demande->nouvelle_date_fin,
                    'duree_jours' => $demande->duree_jours,
                    'motif' => $demande->motif,
                    'commentaire' => $demande->commentaire,
                    'date_soumission' => $demande->date_soumission,
                    'workflow_step' => $demande->workflow_step,
                    'validation_workflow' => $demande->validation_workflow,
                    'current_validator' => $demande->current_validator,
                    'user' => [
                        'id' => $demande->user->id,
                        'name' => $demande->user->nom . ' ' . $demande->user->prenom,
                        'first_name' => $demande->user->prenom,
                        'matricule' => $demande->user->matricule,
                        'email' => $demande->user->email,
                        'department' => $demande->user->department ? [
                            'nom' => $demande->user->department->nom
                        ] : null,
                        'role' => $demande->user->role ? [
                            'nom' => $demande->user->role->nom
                        ] : null
                    ],
                    'form_data' => $demande->form_data,
                    'pieces_jointes' => $demande->pieces_jointes,
                    'signatures' => $demande->signatures,
                    'created_at' => $demande->created_at,
                    'updated_at' => $demande->updated_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $demandesFormatted,
                'message' => count($demandesFormatted) . ' demande(s) de report en attente'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des demandes de report reçues:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir une demande spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $demande = DemandeReport::with(['user.role', 'user.department', 'currentValidator.role', 'validateur.role'])
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
     * Stocker une signature électronique
     */
    private function storeSignature($signatureData, $userId): string
    {
        try {
            // Décoder l'image base64
            $data = explode(',', $signatureData);
            $imageData = base64_decode($data[1]);
            
            // Générer un nom de fichier unique
            $fileName = 'signature_report_' . $userId . '_' . Str::uuid() . '.png';
            $filePath = 'signatures/reports/' . $fileName;
            
            // Sauvegarder le fichier
            Storage::disk('public')->put($filePath, $imageData);
            
            return $filePath;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la sauvegarde de la signature: ' . $e->getMessage());
        }
    }
}
