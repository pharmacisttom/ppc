<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class ColdChainController
{
    /**
     * Cold Chain Units & Monitoring Logs
     */
    public function index(): void
    {
        $db = Database::getAppDb();

        $units = $db->query("
            SELECT u.*,
                   (SELECT COUNT(*) FROM cold_chain_temperature_logs l WHERE l.unit_id = u.unit_id AND l.is_excursion = 1) as total_excursions
            FROM cold_chain_units u
            WHERE u.is_active = 1
        ")->fetchAll();

        // Recent 30 logs
        $logs = $db->query("
            SELECT l.*, u.unit_code, u.unit_name, u.min_temp, u.max_temp, usr.firstname, usr.lastname
            FROM cold_chain_temperature_logs l
            JOIN cold_chain_units u ON l.unit_id = u.unit_id
            JOIN users usr ON l.recorded_by = usr.user_id
            ORDER BY l.record_date DESC, l.session DESC
            LIMIT 50
        ")->fetchAll();

        View::render('coldchain/index', [
            'pageTitle' => 'ระบบควบคุมลูกโซ่ความเย็น (Cold Chain Management 2–8 °C)',
            'units' => $units,
            'logs' => $logs
        ]);
    }

    /**
     * Store new temperature log
     */
    public function store(): void
    {
        $db = Database::getAppDb();
        $user = Auth::user();

        $unitId = (int)Request::post('unit_id');
        $recordDate = Request::post('record_date', date('Y-m-d'));
        $session = Request::post('session', 'morning');
        $currentTemp = (float)Request::post('current_temp');
        $minTemp = (float)Request::post('min_recorded', $currentTemp);
        $maxTemp = (float)Request::post('max_recorded', $currentTemp);
        $correctiveAction = Request::post('corrective_action', '');

        // Excursion check: outside 2.0 - 8.0 °C
        $isExcursion = ($currentTemp < 2.0 || $currentTemp > 8.0) ? 1 : 0;

        try {
            $stmt = $db->prepare("
                INSERT INTO cold_chain_temperature_logs (
                    unit_id, record_date, session, current_temp, 
                    min_recorded, max_recorded, is_excursion, corrective_action, recorded_by
                ) VALUES (
                    :unit_id, :record_date, :session, :current_temp, 
                    :min_recorded, :max_recorded, :is_excursion, :action, :recorded_by
                )
            ");

            $stmt->execute([
                ':unit_id' => $unitId,
                ':record_date' => $recordDate,
                ':session' => $session,
                ':current_temp' => $currentTemp,
                ':min_recorded' => $minTemp,
                ':max_recorded' => $maxTemp,
                ':is_excursion' => $isExcursion,
                ':action' => $isExcursion ? $correctiveAction : null,
                ':recorded_by' => $user['user_id'] ?? 1
            ]);

            Audit::log('COLDCHAIN_LOG', 'coldchain', (string)$unitId, null, null, null, "Recorded temp {$currentTemp}C (Excursion: {$isExcursion})");

            Session::flash('success', 'บันทึกอุณหภูมิตู้เย็นเรียบร้อยแล้ว' . ($isExcursion ? ' ⚠️ ตรวจพบอุณหภูมิหลุดเกณฑ์และบันทึกมาตรการแก้ไขแล้ว' : ''));
            Response::redirect('/hos/cold-chain');
        } catch (\Exception $e) {
            error_log("Coldchain log error: " . $e->getMessage());
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึก: ' . $e->getMessage());
            Response::redirect('/hos/cold-chain');
        }
    }
}
