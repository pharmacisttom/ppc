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
use App\Controllers\DrugController;
use App\Controllers\ReportController;
use App\Controllers\RbacController;
use App\Controllers\SettingsController;
use App\Controllers\DispenseController;
use App\Controllers\SafetyRegistryController;
use App\Controllers\ExchangeController;

// --- Authentication Routes ---
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);

// --- Primary Care Dispensing Workspace Routes (ระบบจ่ายยา - รพ.สต.) ---
Router::get('/dispense', [DispenseController::class, 'index'], ['auth']);
Router::get('/dispense/{pid}', [DispenseController::class, 'index'], ['auth']);
Router::get('/dispense/print/{pid}', [DispenseController::class, 'printStickers'], ['auth']);
Router::get('/api/dispense/{pid}', [DispenseController::class, 'apiDetail'], ['auth']);

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
Router::get('/inventory/jhcis-stores', [InventoryController::class, 'jhcisStoreDashboard'], ['auth']);
Router::get('/inventory/jhcis-stores/export', [InventoryController::class, 'exportDualStoreExcel'], ['auth']);
Router::get('/inventory/rb301', [InventoryController::class, 'rb301'], ['auth']);
Router::get('/inventory/rb301/export', [InventoryController::class, 'exportRb301Excel'], ['auth']);
Router::get('/inventory/movements', [InventoryController::class, 'movements'], ['auth']);
Router::get('/inventory/expiry', [InventoryController::class, 'expiryDashboard'], ['auth']);

// --- Cold Chain Management Routes ---
Router::get('/cold-chain', [ColdChainController::class, 'index'], ['auth']);
Router::post('/cold-chain/store', [ColdChainController::class, 'store'], ['auth', 'csrf']);

// --- PCU Medication Safety Center & Clinical Registries ---
Router::get('/safety', [SafetyController::class, 'index'], ['auth']);
Router::get('/safety/incidents', [SafetyController::class, 'incidents'], ['auth']);
Router::get('/safety/incidents/create', [SafetyController::class, 'createIncident'], ['auth']);
Router::post('/safety/incidents', [SafetyController::class, 'storeIncident'], ['auth', 'csrf']);
Router::get('/safety/incidents/export', [SafetyController::class, 'exportIncidents'], ['auth']);
Router::get('/safety/incidents/{id}', [SafetyController::class, 'showIncident'], ['auth']);
Router::post('/safety/incidents/{id}/rca', [SafetyController::class, 'updateRca'], ['auth', 'csrf']);

// 4 Clinical Safety Registries & VHV
Router::get('/safety/allergies', [SafetyRegistryController::class, 'allergies'], ['auth']);
Router::get('/safety/allergies/card/{pid}', [SafetyRegistryController::class, 'printAllergyCard'], ['auth']);
Router::get('/safety/warfarin', [SafetyRegistryController::class, 'warfarin'], ['auth']);
Router::get('/safety/anticonvulsant', [SafetyRegistryController::class, 'anticonvulsant'], ['auth']);
Router::get('/safety/ckd', [SafetyRegistryController::class, 'ckd'], ['auth']);
Router::get('/safety/g6pd', [SafetyRegistryController::class, 'g6pd'], ['auth']);
Router::get('/safety/vhv', [SafetyRegistryController::class, 'vhv'], ['auth']);
Router::get('/vhv', [SafetyRegistryController::class, 'vhv'], ['auth']);

Router::get('/safety/ham-lasa', [SafetyController::class, 'hamLasa'], ['auth']);
Router::get('/safety/emergency-kit', [SafetyController::class, 'emergencyKit'], ['auth']);
Router::post('/safety/emergency-kit/inspect', [SafetyController::class, 'inspectEmergencyKit'], ['auth', 'csrf']);

// --- MOPH Standard 43 Files & CUP Hospital Data Exchange Center ---
Router::get('/exchange', [ExchangeController::class, 'index'], ['auth']);
Router::get('/exchange/file/{type}', [ExchangeController::class, 'downloadFile'], ['auth']);
Router::get('/exchange/zip', [ExchangeController::class, 'downloadZip'], ['auth']);
Router::get('/exchange/excel', [ExchangeController::class, 'exportCupExcel'], ['auth']);

// --- Quality Standards 2568-2570 & Evidence Center Routes ---
Router::get('/quality', [QualityController::class, 'index'], ['auth']);
Router::post('/quality/assess', [QualityController::class, 'updateAssessment'], ['auth', 'csrf']);
Router::get('/quality/evidence', [QualityController::class, 'evidence'], ['auth']);

// --- Quality KPIs & RDU Antibiotic Stewardship Routes ---
Router::get('/kpi', [KpiController::class, 'index'], ['auth']);
Router::get('/kpi/rdu', [KpiController::class, 'rduDashboard'], ['auth']);

// --- PCU Drug Formulary & Medication Catalog Routes ---
Router::get('/drugs', [DrugController::class, 'index'], ['auth']);
Router::get('/drugs/print', [DrugController::class, 'printFormulary'], ['auth']);
Router::get('/drugs/export-excel', [DrugController::class, 'exportExcel'], ['auth']);
Router::get('/drugs/export-csv', [DrugController::class, 'exportCsv'], ['auth']);
Router::get('/drugs/{code}', [DrugController::class, 'show'], ['auth']);
Router::get('/api/drugs', [DrugController::class, 'apiCatalog'], ['auth']);
Router::get('/api/drugs/{code}', [DrugController::class, 'apiDetail'], ['auth']);

// --- Reporting & Excel Export Center Routes ---
Router::get('/reports', [ReportController::class, 'index'], ['auth']);
Router::get('/reports/inventory-excel', [ReportController::class, 'exportInventory'], ['auth']);
Router::get('/reports/coldchain-excel', [ReportController::class, 'exportColdChain'], ['auth']);
Router::get('/reports/ham-lasa-excel', [ReportController::class, 'exportHamLasa'], ['auth']);
Router::get('/reports/drp-excel', [ReportController::class, 'exportDrp'], ['auth']);
Router::get('/reports/quality-excel', [ReportController::class, 'exportQuality'], ['auth']);

// --- Role-Based Access Control (RBAC) & User Management Routes ---
Router::get('/rbac', [RbacController::class, 'index'], ['auth']);
Router::get('/rbac/matrix', [RbacController::class, 'matrix'], ['auth']);
Router::post('/rbac/assign-role', [RbacController::class, 'assignRole'], ['auth', 'csrf']);
Router::post('/rbac/create-user', [RbacController::class, 'createUser'], ['auth', 'csrf']);
Router::post('/rbac/update-user', [RbacController::class, 'updateUser'], ['auth', 'csrf']);
Router::post('/rbac/toggle-status', [RbacController::class, 'toggleUserStatus'], ['auth', 'csrf']);
Router::get('/rbac/switch', [RbacController::class, 'switchPersona'], ['auth']);
Router::get('/audit-logs', [RbacController::class, 'auditLogs'], ['auth']);

// --- Smart Base System Settings Routes ---
Router::get('/settings', [SettingsController::class, 'index'], ['auth']);
Router::post('/settings/save', [SettingsController::class, 'save'], ['auth']);
Router::post('/settings/auto-sync-jhcis', [SettingsController::class, 'autoSyncJhcis'], ['auth']);
Router::get('/settings/diagnostics', [SettingsController::class, 'diagnostics'], ['auth']);
Router::get('/settings/export', [SettingsController::class, 'exportConfig'], ['auth']);
Router::post('/settings/import', [SettingsController::class, 'importConfig'], ['auth']);
Router::get('/settings/download-sql', [SettingsController::class, 'downloadSql'], ['auth']);
Router::post('/settings/install-db', [SettingsController::class, 'installDb'], ['auth', 'csrf']);

// Master Data Updates for Other Core Modules
Router::post('/drugs/update-master', [DrugController::class, 'updateMaster'], ['auth', 'csrf']);
Router::post('/cold-chain/update-unit', [ColdChainController::class, 'updateUnit'], ['auth', 'csrf']);
Router::post('/safety/ckd/update', [SafetyRegistryController::class, 'updateCkd'], ['auth', 'csrf']);
Router::post('/safety/anticonvulsant/update', [SafetyRegistryController::class, 'updateAnticonvulsant'], ['auth', 'csrf']);
Router::post('/safety/allergies/update-note', [SafetyRegistryController::class, 'updateAllergyNote'], ['auth', 'csrf']);



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
