<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DemandeCongeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LeavePlanController;
use App\Http\Controllers\Api\HolidayController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes publiques avec limitation des tentatives
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Dashboard routes
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
    Route::get('/dashboard/stats-manager', [DashboardController::class, 'statsManager']);

    // Routes spécifiques AVANT les ressources
    Route::get('/demandes-conges/recues', [DemandeCongeController::class, 'demandesRecues']);
    Route::post('/demandes-conges/submit-with-workflow', [DemandeCongeController::class, 'submitWithWorkflow']);
    Route::post('/demandes-conges/validate-with-next', [DemandeCongeController::class, 'validateWithNext']);
    Route::get('/users/search-by-email', [DemandeCongeController::class, 'searchUsersByEmail']);
    Route::get('/users/search-by-name', [DemandeCongeController::class, 'searchUsersByName']);

    // Demandes de congés (ressource générale)
    Route::apiResource('demandes-conges', DemandeCongeController::class);
    Route::post('/demandes-conges/{demandeConge}/validate', [DemandeCongeController::class, 'validateDemande']);
    Route::get('/demandes-a-valider', [DemandeCongeController::class, 'demandesAValider']);

    // Gestion des utilisateurs (Admin)
    Route::apiResource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/users/{user}/simulate-login', [UserController::class, 'simulateLogin']);
    Route::get('/managers', [UserController::class, 'getManagers']);
//    localhost:8000/users?page=1&limit=10
    // Gestion des rôles
    Route::apiResource('roles', RoleController::class);

    // Gestion des départements
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/departments/{department}/stats', [DepartmentController::class, 'stats']);

    // Gestion des périodes de congés et jours fériés (Admin)
    Route::apiResource('leave-plans', LeavePlanController::class);
    Route::apiResource('holidays', HolidayController::class);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

});

// Route de test
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Laravel fonctionne correctement',
        'timestamp' => now(),
    ]);
});

// Routes pour la documentation API
Route::get('/openapi.json', [App\Http\Controllers\ApiDocController::class, 'openapi']);
Route::get('/health', [App\Http\Controllers\ApiDocController::class, 'health']);
