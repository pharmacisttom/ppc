<?php
/**
 * PCU Smart Pharmacy & Medication Management System
 * Single Front Controller & Router Dispatcher
 */

declare(strict_types=1);

require_once __DIR__ . '/app/Core/App.php';

// Boot Application Core (Autoloader, Env, Timezone, Session)
\App\Core\App::boot();

use App\Core\Router;
use App\Core\Response;
use App\Core\Database;
use App\Gateway\JhcisGateway;
use App\Services\SafetyEngine;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PatientController;
use App\Controllers\MedicationReviewController;
use App\Controllers\InventoryController;
use App\Controllers\ColdChainController;
use App\Controllers\SafetyController;
use App\Controllers\QualityController;
use App\Controllers\KpiController;

// --- Authentication Routes ---
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);

// --- Dashboard Routes ---
Router::get('/', [DashboardController::class, 'index'], ['auth']);
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// --- Patient & Clinical Safety Routes ---
Router::get('/patients', [PatientController::class, 'index'], ['auth']);
Router::get('/patients/{pid}', [PatientController::class, 'profile'], ['auth']);

// --- Medication Review & DRP Routes ---
Router::get('/reviews', [MedicationReviewController::class, 'index'], ['auth']);
Router::get('/reviews/create', [MedicationReviewController::class, 'create'], ['auth']);
Router::post('/reviews/store', [MedicationReviewController::class, 'store'], ['auth', 'csrf']);
Router::get('/reviews/{id}', [MedicationReviewController::class, 'show'], ['auth']);

// --- Inventory, Stock Card & FEFO Routes ---
Router::get('/inventory', [InventoryController::class, 'index'], ['auth']);
Router::get('/inventory/movements', [InventoryController::class, 'movements'], ['auth']);
Router::get('/inventory/expiry', [InventoryController::class, 'expiryDashboard'], ['auth']);

// --- Cold Chain Management Routes ---
Router::get('/cold-chain', [ColdChainController::class, 'index'], ['auth']);
Router::post('/cold-chain/store', [ColdChainController::class, 'store'], ['auth', 'csrf']);

// --- Medication Safety, HAM/LASA & CPR Kit Routes ---
Router::get('/safety/ham-lasa', [SafetyController::class, 'hamLasa'], ['auth']);
Router::get('/safety/emergency-kit', [SafetyController::class, 'emergencyKit'], ['auth']);

// --- Quality Standards 2568-2570 & Evidence Center Routes ---
Router::get('/quality', [QualityController::class, 'index'], ['auth']);
Router::post('/quality/assess', [QualityController::class, 'updateAssessment'], ['auth', 'csrf']);
Router::get('/quality/evidence', [QualityController::class, 'evidence'], ['auth']);

// --- Quality KPIs & RDU Antibiotic Stewardship Routes ---
Router::get('/kpi', [KpiController::class, 'index'], ['auth']);
Router::get('/kpi/rdu', [KpiController::class, 'rduDashboard'], ['auth']);

// --- System & Health API Routes ---
Router::get('/api/health', function () {
    $appDbOk = false;
    $jhcisDbOk = false;
    $jhcisDrugCount = 0;

    try {
        $db = Database::getAppDb();
        $appDbOk = $db->query("SELECT 1")->fetchColumn() === 1;
    } catch (\Throwable $e) {}

    try {
        $jhcis = Database::getJhcisDb();
        if ($jhcis) {
            $jhcisDbOk = true;
            $jhcisDrugCount = (int)$jhcis->query("SELECT count(*) FROM cdrug")->fetchColumn();
        }
    } catch (\Throwable $e) {}

    Response::json([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'environment' => getenv('APP_ENV') ?: 'local',
        'mode' => JhcisGateway::isTrainingMode() ? 'training' : 'production',
        'database' => [
            'app_db_3306' => $appDbOk ? 'connected' : 'disconnected',
            'jhcis_gateway_3333' => $jhcisDbOk ? 'connected' : 'offline',
            'jhcis_drug_catalog' => $jhcisDrugCount
        ]
    ]);
});

Router::get('/api/patients/{pid}/safety', function ($pid) {
    $pidInt = (int)$pid;
    $patient = JhcisGateway::getPatient($pidInt);
    if (!$patient) {
        Response::json(['error' => 'Patient not found'], 404);
        return;
    }

    $allergies = JhcisGateway::getPatientAllergies($pidInt);
    $chronicConditions = JhcisGateway::getPatientChronicDiseases($pidInt);
    $meds = $patient['current_medications'] ?? [];
    $safetyFlags = SafetyEngine::screenPatient($patient, $allergies, $chronicConditions, $meds);

    Response::json([
        'pid' => $pidInt,
        'full_name' => $patient['full_name'],
        'safety_flags' => $safetyFlags
    ]);
}, ['auth']);

// Dispatch HTTP Request
Router::dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
