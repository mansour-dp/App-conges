<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $holidays = Holiday::orderBy('date')->get();
            
            return response()->json([
                'success' => true,
                'data' => $holidays
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des jours fériés'
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
                'date' => 'required|date',
                'type' => ['required', Rule::in(['national', 'religious', 'local', 'company'])],
                'description' => 'nullable|string',
                'is_recurring' => 'boolean'
            ]);

            $holiday = Holiday::create($validated);

            return response()->json([
                'success' => true,
                'data' => $holiday,
                'message' => 'Jour férié créé avec succès'
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
                'message' => 'Erreur lors de la création du jour férié'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $holiday
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'date' => 'required|date',
                'type' => ['required', Rule::in(['national', 'religious', 'local', 'company'])],
                'description' => 'nullable|string',
                'is_recurring' => 'boolean'
            ]);

            $holiday->update($validated);

            return response()->json([
                'success' => true,
                'data' => $holiday,
                'message' => 'Jour férié mis à jour avec succès'
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
                'message' => 'Erreur lors de la mise à jour du jour férié'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday): JsonResponse
    {
        try {
            $holiday->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jour férié supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du jour férié'
            ], 500);
        }
    }
}
