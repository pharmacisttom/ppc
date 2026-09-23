<?php
namespace App\Services;

use App\Core\Database;
use PDO;
use Exception;

class SystemSettingService
{
    private static array $cache = [];

    /**
     * Get a setting value with type casting
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $db = Database::getAppDb();
            $stmt = $db->prepare("SELECT setting_value, data_type FROM system_settings WHERE setting_key = ? LIMIT 1");
            $stmt->execute([$key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $default;
            }

            $val = self::castValue($row['setting_value'], $row['data_type']);
            self::$cache[$key] = $val;
            return $val;
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?int $userId = null): bool
    {
        try {
            $db = Database::getAppDb();
            
            // Format value for storage
            $strValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;

            if ($group !== null) {
                $stmt = $db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, setting_group, updated_by)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        setting_value = VALUES(setting_value),
                        setting_group = VALUES(setting_group),
                        updated_by = VALUES(updated_by)
                ");
                $result = $stmt->execute([$key, $strValue, $group, $userId]);
            } else {
                $stmt = $db->prepare("
                    UPDATE system_settings
                    SET setting_value = ?, updated_by = ?
                    WHERE setting_key = ?
                ");
                $result = $stmt->execute([$strValue, $userId, $key]);
            }

            self::$cache[$key] = $value;
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all settings grouped by group name
     */
    public static function getAllByGroup(?string $targetGroup = null): array
    {
        $db = Database::getAppDb();
        if ($targetGroup) {
            $stmt = $db->prepare("SELECT * FROM system_settings WHERE setting_group = ? ORDER BY setting_key ASC");
            $stmt->execute([$targetGroup]);
        } else {
            $stmt = $db->query("SELECT * FROM system_settings ORDER BY setting_group ASC, setting_key ASC");
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $grouped = [];

        foreach ($rows as $row) {
            $row['typed_value'] = self::castValue($row['setting_value'], $row['data_type']);
            self::$cache[$row['setting_key']] = $row['typed_value'];
            $grouped[$row['setting_group']][$row['setting_key']] = $row;
        }

        return $grouped;
    }

    /**
     * Smart Auto-Sync: Detect and sync facility data from connected JHCIS
     */
    public static function autoSyncFromJhcis(?string $targetHospCode = null): array
    {
        $jhcis = Database::getJhcisDb();
        $appDb = Database::getAppDb();

        // 1. Identify primary PCU code if not given
        if (!$targetHospCode) {
            try {
                $stmt = $jhcis->query("SELECT pcucode FROM person WHERE pcucode IS NOT NULL AND pcucode != '' GROUP BY pcucode ORDER BY COUNT(*) DESC LIMIT 1");
                $pcu = $stmt->fetchColumn();
                $targetHospCode = $pcu ?: '01996';
            } catch (Exception $e) {
                $targetHospCode = '01996';
            }
        }

        // 2. Query chospital
        $stmt = $jhcis->prepare("SELECT * FROM chospital WHERE hoscode = ? LIMIT 1");
        $stmt->execute([$targetHospCode]);
        $hosp = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$hosp) {
            throw new Exception("ไม่พบข้อมูลรหัสสถานพยาบาล {$targetHospCode} ในตาราง chospital ของ JHCIS");
        }

        // 3. Update facilities table in App DB
        $checkFac = $appDb->query("SELECT facility_id FROM facilities ORDER BY facility_id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        if ($checkFac) {
            $upd = $appDb->prepare("
                UPDATE facilities
                SET facility_code = ?, facility_name = ?, district_code = ?, province_code = ?, parent_hospital_code = ?
                WHERE facility_id = ?
            ");
            $upd->execute([
                $hosp['hoscode'],
                $hosp['hosname'] ?: 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย',
                $hosp['distcode'] ? $hosp['provcode'] . $hosp['distcode'] : '2106',
                $hosp['provcode'] ?: '21',
                '10670',
                $checkFac['facility_id']
            ]);
        }

        // 4. Update system_settings keys
        $updates = [
            'facility_code' => $hosp['hoscode'],
            'facility_code_9' => $hosp['hoscodenew'] ?? '000199600',
            'facility_name' => $hosp['hosname'] ?: 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย',
            'health_zone' => $hosp['ketzone'] ?? '06',
            'province_code' => $hosp['provcode'] ?? '21',
            'district_code' => $hosp['distcode'] ?? '06',
            'subdistrict_code' => $hosp['subdistcode'] ?? '04',
            'village_no' => $hosp['mu'] ?? '06',
            'facility_address' => trim(($hosp['address'] ?? '') . ' ' . ($hosp['road'] ?? '')),
            'parent_hospital_code' => '10670',
            'parent_hospital_name' => 'โรงพยาบาลระยอง (แม่ข่าย CUP)'
        ];

        foreach ($updates as $k => $v) {
            self::set($k, $v, 'facility');
            $appDb->prepare("UPDATE system_settings SET is_smart_detected = 1 WHERE setting_key = ?")->execute([$k]);
        }

        return [
            'status' => 'success',
            'hospcode' => $hosp['hoscode'],
            'name' => $hosp['hosname'],
            'zone' => $hosp['ketzone'] ?? '06',
            'province' => $hosp['provcode'] ?? '21',
            'district' => $hosp['distcode'] ?? '06',
            'subdistrict' => $hosp['subdistcode'] ?? '04',
            'updated_keys' => array_keys($updates)
        ];
    }

    /**
     * Run Live System Diagnostics & Connection Quality Check
     */
    public static function getDiagnostics(): array
    {
        $result = [
            'timestamp' => date('Y-m-d H:i:s'),
            'app_db' => ['status' => 'unknown', 'latency_ms' => null, 'tables_count' => 0],
            'jhcis_db' => ['status' => 'unknown', 'latency_ms' => null, 'stats' => []],
            'server' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time') . 's',
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
                'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Standby'
            ]
        ];

        // 1. App DB Check
        try {
            $t0 = microtime(true);
            $appDb = Database::getAppDb();
            $cnt = $appDb->query("SHOW TABLES")->rowCount();
            $latency = round((microtime(true) - $t0) * 1000, 2);

            $result['app_db'] = [
                'status' => 'connected',
                'host' => '127.0.0.1:3306',
                'database' => 'pcu_pharmacy',
                'tables_count' => $cnt,
                'latency_ms' => $latency
            ];
        } catch (Exception $e) {
            $result['app_db'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        // 2. JHCIS DB Check
        try {
            $t0 = microtime(true);
            $jhcis = Database::getJhcisDb();
            $tables = $jhcis->query("SHOW TABLES")->rowCount();
            $latency = round((microtime(true) - $t0) * 1000, 2);

            // Fetch quick row counts
            $drugCount = (int)$jhcis->query("SELECT COUNT(*) FROM cdrug")->fetchColumn();
            $personCount = (int)$jhcis->query("SELECT COUNT(*) FROM person")->fetchColumn();
            $visitCount = (int)$jhcis->query("SELECT COUNT(*) FROM visit")->fetchColumn();
            $rxCount = (int)$jhcis->query("SELECT COUNT(*) FROM visitdrug")->fetchColumn();
            $receiveCount = (int)$jhcis->query("SELECT COUNT(*) FROM drugstorereceive")->fetchColumn();
            $transferCount = (int)$jhcis->query("SELECT COUNT(*) FROM drugrepositoryout")->fetchColumn();

            $result['jhcis_db'] = [
                'status' => 'connected',
                'host' => '127.0.0.1:3333',
                'database' => 'jhcisdb',
                'tables_count' => $tables,
                'latency_ms' => $latency,
                'stats' => [
                    'drugs' => $drugCount,
                    'persons' => $personCount,
                    'visits' => $visitCount,
                    'prescriptions' => $rxCount,
                    'main_store_receives' => $receiveCount,
                    'dispensary_transfers' => $transferCount
                ]
            ];
        } catch (Exception $e) {
            $result['jhcis_db'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * Export all configuration as JSON
     */
    public static function exportConfigJson(): string
    {
        $db = Database::getAppDb();
        $stmt = $db->query("SELECT setting_key, setting_value, setting_group, data_type, description, is_smart_detected FROM system_settings");
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $payload = [
            'system' => 'PCU Smart Pharmacy & JHCIS Dual-Store Inventory System',
            'exported_at' => date('Y-m-d H:i:s'),
            'facility_code' => self::get('facility_code', '01996'),
            'facility_name' => self::get('facility_name', 'โรงพยาบาลส่งเสริมสุขภาพตำบลบ้านดอกกราย'),
            'settings' => $all
        ];

        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import configuration from JSON
     */
    public static function importConfigJson(string $jsonString, ?int $userId = null): array
    {
        $data = json_decode($jsonString, true);
        if (!$data || !isset($data['settings']) || !is_array($data['settings'])) {
            throw new Exception("รูปแบบไฟล์ JSON ไม่ถูกต้อง ไม่พบโครงสร้างการตั้งค่าระบบ");
        }

        $appDb = Database::getAppDb();
        $stmt = $appDb->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_group, data_type, description, is_smart_detected, updated_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                setting_group = VALUES(setting_group),
                data_type = VALUES(data_type),
                description = VALUES(description),
                updated_by = VALUES(updated_by)
        ");

        $imported = 0;
        foreach ($data['settings'] as $s) {
            if (isset($s['setting_key'])) {
                $stmt->execute([
                    $s['setting_key'],
                    $s['setting_value'] ?? '',
                    $s['setting_group'] ?? 'general',
                    $s['data_type'] ?? 'string',
                    $s['description'] ?? '',
                    $s['is_smart_detected'] ?? 0,
                    $userId
                ]);
                self::$cache[$s['setting_key']] = self::castValue($s['setting_value'] ?? '', $s['data_type'] ?? 'string');
                $imported++;
            }
        }

        return ['imported_count' => $imported];
    }

    /**
     * Cast string database value into appropriate PHP data type
     */
    private static function castValue(?string $val, string $type): mixed
    {
        if ($val === null) return null;
        return match ($type) {
            'number' => str_contains($val, '.') ? (float)$val : (int)$val,
            'boolean' => filter_var($val, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($val, true) ?? [],
            default => $val
        };
    }
}
