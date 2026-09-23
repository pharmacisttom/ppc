<?php
namespace App\Core;

use PDO;
use Exception;

/**
 * Enterprise Healthcare Audit Logger (PDPA & Compliance)
 */
class Audit
{
    /**
     * Record an audit event into audit_logs table
     */
    public static function log(
        string $actionType,
        string $moduleName,
        ?string $recordId = null,
        ?int $patientPid = null,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null
    ): void {
        try {
            $db = Database::getAppDb();
            $userId = Session::get('user_id');
            $facilityId = Session::get('facility_id') ?: 1;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

            $stmt = $db->prepare("
                INSERT INTO audit_logs (
                    user_id, facility_id, action_type, module_name, record_id, 
                    patient_pid, ip_address, user_agent, payload_before, payload_after, reason
                ) VALUES (
                    :user_id, :facility_id, :action_type, :module_name, :record_id, 
                    :patient_pid, :ip_address, :user_agent, :payload_before, :payload_after, :reason
                )
            ");

            $stmt->execute([
                ':user_id' => $userId,
                ':facility_id' => $facilityId,
                ':action_type' => $actionType,
                ':module_name' => $moduleName,
                ':record_id' => $recordId,
                ':patient_pid' => $patientPid,
                ':ip_address' => $ip,
                ':user_agent' => $userAgent,
                ':payload_before' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
                ':payload_after' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
                ':reason' => $reason
            ]);
        } catch (Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
        }
    }
}
