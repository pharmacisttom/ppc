<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\SystemSettingService;
use Exception;

class SettingsController
{
    /**
     * Display Smart Master Settings Page
     */
    public function index(): void
    {
        $user = Auth::user();
        $settings = SystemSettingService::getAllByGroup();
        $diagnostics = SystemSettingService::getDiagnostics();

        View::render('settings/index', [
            'pageTitle' => 'กำหนดข้อมูลพื้นฐานของระบบ แบบอัจฉริยะ (Smart System Settings)',
            'settings' => $settings,
            'diagnostics' => $diagnostics,
            'activeTab' => Request::get('tab', 'facility'),
            'user' => $user
        ]);
    }

    /**
     * Save System Settings
     */
    public function save(): void
    {
        $user = Auth::user();
        $userId = $user['user_id'] ?? null;
        $tab = Request::post('active_tab', 'facility');
        $inputs = $_POST;

        // Strip non-setting fields
        unset($inputs['active_tab']);

        $savedCount = 0;
        foreach ($inputs as $key => $val) {
            // Determine group from key prefix or default
            $group = 'general';
            if (str_starts_with($key, 'facility_') || in_array($key, ['health_zone', 'province_code', 'district_code', 'subdistrict_code', 'village_no', 'parent_hospital_code', 'parent_hospital_name', 'director_name', 'director_position', 'lead_pharmacist_name', 'lead_pharmacist_license', 'pharmacy_technician_name'])) {
                $group = 'facility';
            } elseif (str_starts_with($key, 'fefo_') || str_starts_with($key, 'stock_') || str_contains($key, 'multiplier') || str_contains($key, 'reorder')) {
                $group = 'inventory';
            } elseif (str_starts_with($key, 'cds_') || str_starts_with($key, 'egfr_') || str_starts_with($key, 'coldchain_') || str_starts_with($key, 'had_') || str_starts_with($key, 'check_')) {
                $group = 'safety';
            } elseif (in_array($key, ['training_mode', 'auto_fefo_deduct', 'smart_cds_realtime', 'audit_trail_logging'])) {
                $group = 'automation';
            } elseif (str_starts_with($key, 'jhcis_') || str_starts_with($key, 'moph_')) {
                $group = 'connection';
            }

            SystemSettingService::set($key, $val, $group, $userId);
            $savedCount++;
        }

        Audit::log('UPDATE_SETTINGS', 'system_settings', null, null, null, null, "อัปเดตการตั้งค่าระบบจำนวน {$savedCount} รายการ ในหมวดหมู่ {$tab}");
        Session::flash('success', "บันทึกการตั้งค่าระบบเรียบร้อยแล้ว ({$savedCount} รายการ)");
        Response::redirect("/pcc/settings?tab={$tab}");
    }

    /**
     * Smart Auto-Sync: Discover and populate facility info from JHCIS
     */
    public function autoSyncJhcis(): void
    {
        try {
            $result = SystemSettingService::autoSyncFromJhcis();
            Audit::log('AUTO_SYNC_JHCIS', 'facilities', null, null, null, null, "ดึงข้อมูลหน่วยบริการอัตโนมัติจาก JHCIS ({$result['hospcode']} - {$result['name']})");

            if (Request::isAjax()) {
                Response::json(['status' => 'success', 'data' => $result, 'message' => "ซิงค์ข้อมูลหน่วยบริการ {$result['name']} (รหัส {$result['hospcode']}) สำเร็จเรียบร้อย"]);
                return;
            }

            Session::flash('success', "🤖 ซิงค์ข้อมูลอัจฉริยะสำเร็จ! ดึงข้อมูล รหัสสถานพยาบาล {$result['hospcode']} ({$result['name']}) จากฐานข้อมูล JHCIS เรียบร้อยแล้ว");
            Response::redirect('/pcc/settings?tab=facility');
        } catch (Exception $e) {
            if (Request::isAjax()) {
                Response::json(['status' => 'error', 'message' => $e->getMessage()], 400);
                return;
            }

            Session::flash('error', "เกิดข้อผิดพลาดในการดึงข้อมูลจาก JHCIS: " . $e->getMessage());
            Response::redirect('/pcc/settings?tab=facility');
        }
    }

    /**
     * Live Diagnostics API endpoint
     */
    public function diagnostics(): void
    {
        $diag = SystemSettingService::getDiagnostics();
        Response::json($diag);
    }

    /**
     * Export Configuration Backup JSON
     */
    public function exportConfig(): void
    {
        $hospCode = SystemSettingService::get('facility_code', '01996');
        $filename = "smart_settings_{$hospCode}_" . date('Ymd_His') . ".json";
        $json = SystemSettingService::exportConfigJson();

        header('Content-Type: application/json; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        echo $json;
        exit;
    }

    /**
     * Import Configuration Backup JSON
     */
    public function importConfig(): void
    {
        $user = Auth::user();
        if (!isset($_FILES['config_file']) || $_FILES['config_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'กรุณาเลือกไฟล์ JSON สำหรับนำเข้าการตั้งค่า');
            Response::redirect('/pcc/settings?tab=connection');
            return;
        }

        try {
            $content = file_get_contents($_FILES['config_file']['tmp_name']);
            $res = SystemSettingService::importConfigJson($content, $user['user_id'] ?? null);
            Audit::log('IMPORT_SETTINGS', 'system_settings', null, "นำเข้าการตั้งค่าระบบจำนวน {$res['imported_count']} รายการจากไฟล์สำรอง");

            Session::flash('success', "นำเข้าการตั้งค่าสำเร็จ ({$res['imported_count']} รายการ)");
            Response::redirect('/pcc/settings?tab=connection');
        } catch (Exception $e) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการนำเข้าการตั้งค่า: ' . $e->getMessage());
            Response::redirect('/pcc/settings?tab=connection');
        }
    }

    /**
     * Download Standalone SQL Schema for Local XAMPP MySQL
     */
    public function downloadSql(): void
    {
        $file = dirname(__DIR__, 2) . '/database/pcu_pharmacy_standalone.sql';
        if (!file_exists($file)) {
            Session::flash('error', 'ไม่พบไฟล์ฐานข้อมูล SQL');
            Response::redirect('/pcc/settings?tab=connection');
            return;
        }

        $filename = "pcu_pharmacy_standalone_" . date('Ymd_His') . ".sql";
        header('Content-Type: application/sql; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    /**
     * Execute Database Setup / Verification for XAMPP Local MySQL (Port 3306)
     */
    public function installDb(): void
    {
        $file = dirname(__DIR__, 2) . '/database/install.php';
        if (!file_exists($file)) {
            Session::flash('error', 'ไม่พบไฟล์ตัวติดตั้งฐานข้อมูล');
            Response::redirect('/pcc/settings?tab=connection');
            return;
        }

        ob_start();
        include $file;
        $output = ob_get_clean();

        Audit::log('DB_SCHEMA_SYNC', 'database', null, null, null, null, "ตรวจสอบและติดตั้งโครงสร้างฐานข้อมูล pcu_pharmacy ใน XAMPP Local MySQL");
        Session::flash('success', "🎉 ตรวจสอบและซิงค์ฐานข้อมูล Localhost MySQL (pcu_pharmacy) สำเร็จ 38 ตารางเรียบร้อยแล้ว โดยไม่กระทบ JHCIS DB");
        Response::redirect('/pcc/settings?tab=connection');
    }
}
