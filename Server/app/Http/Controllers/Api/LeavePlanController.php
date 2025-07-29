<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeavePlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class LeavePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $leavePlans = LeavePlan::orderBy('start_date')->get();
            
            return response()->json([
                'success' => true,
                'data' => $leavePlans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des périodes de congés'
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_type' => ['required', Rule::in([
                    'conge_annuel',
                    'conges_fractionnes',
                    'autres_conges_legaux',
                    'conge_maladie',
                    'conge_maternite',
                    'conge_paternite',
                    'conge_sans_solde',
                    'absence_exceptionnelle',
                    'report_conge'
                ])],
                'description' => 'nullable|string'
            ]);

            $leavePlan = LeavePlan::create($validated);

            return response()->json([
                'success' => true,
                'data' => $leavePlan,
                'message' => 'Période de congés créée avec succès'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la période de congés'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeavePlan $leavePlan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $leavePlan
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeavePlan $leavePlan): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'leave_type' => ['required', Rule::in([
                    'conge_annuel',
                    'conges_fractionnes',
                    'autres_conges_legaux',
                    'conge_maladie',
                    'conge_maternite',
                    'conge_paternite',
                    'conge_sans_solde',
                    'absence_exceptionnelle',
                    'report_conge'
                ])],
                'description' => 'nullable|string'
            ]);

            $leavePlan->update($validated);

            return response()->json([
                'success' => true,
                'data' => $leavePlan,
                'message' => 'Période de congés mise à jour avec succès'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la période de congés'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeavePlan $leavePlan): JsonResponse
    {
        try {
            $leavePlan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Période de congés supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la période de congés'
            ], 500);
        }
    }
}
