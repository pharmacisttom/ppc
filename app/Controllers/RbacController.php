<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Audit;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use PDO;

class RbacController
{
    /**
     * User & Role Management Dashboard
     */
    public function index(): void
    {
        $db = Database::getAppDb();

        // Fetch all users with their roles, access level, and facility
        $users = $db->query("
            SELECT u.*, f.facility_name, f.facility_code,
                   GROUP_CONCAT(r.role_id) as role_ids,
                   GROUP_CONCAT(r.role_name SEPARATOR ', ') as role_names,
                   GROUP_CONCAT(r.display_name SEPARATOR ', ') as display_roles,
                   MIN(r.access_level) as highest_level,
                   GROUP_CONCAT(DISTINCT r.level_name SEPARATOR '; ') as level_names
            FROM users u
            JOIN facilities f ON u.facility_id = f.facility_id
            LEFT JOIN user_roles ur ON u.user_id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.role_id
            GROUP BY u.user_id
            ORDER BY highest_level ASC, u.user_id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all roles grouped by level
        $roles = $db->query("
            SELECT r.*,
                   (SELECT COUNT(*) FROM user_roles ur WHERE ur.role_id = r.role_id) as user_count,
                   (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.role_id) as permission_count
            FROM roles r
            ORDER BY r.access_level ASC, r.role_id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch facilities for dropdown
        $facilities = $db->query("SELECT * FROM facilities WHERE is_active = 1 ORDER BY facility_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Level distribution stats
        $levelStats = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($users as $u) {
            $lvl = (int)($u['highest_level'] ?? 3);
            if (isset($levelStats[$lvl])) {
                $levelStats[$lvl]++;
            }
        }

        View::render('rbac/index', [
            'pageTitle' => 'การกำหนดระดับสิทธิ์และบทบาทผู้ใช้งาน (RBAC Management)',
            'users' => $users,
            'roles' => $roles,
            'facilities' => $facilities,
            'levelStats' => $levelStats,
            'currentUser' => Auth::user()
        ]);
    }

    /**
     * Interactive Permission Matrix View
     */
    public function matrix(): void
    {
        $db = Database::getAppDb();

        $roles = $db->query("SELECT * FROM roles ORDER BY access_level ASC, role_id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $permissions = $db->query("SELECT * FROM permissions ORDER BY module_name ASC, permission_code ASC")->fetchAll(PDO::FETCH_ASSOC);

        $rpRows = $db->query("SELECT role_id, permission_id FROM role_permissions")->fetchAll(PDO::FETCH_ASSOC);
        $rolePermMap = [];
        foreach ($rpRows as $rp) {
            $rolePermMap[$rp['role_id']][$rp['permission_id']] = true;
        }

        View::render('rbac/matrix', [
            'pageTitle' => 'เมทริกซ์สิทธิ์การเข้าถึงระบบยา (Permission Matrix)',
            'roles' => $roles,
            'permissions' => $permissions,
            'rolePermMap' => $rolePermMap
        ]);
    }

    /**
     * Assign / Change User Roles
     */
    public function assignRole(): void
    {
        $userId = (int)Request::post('user_id', 0);
        $roleIds = Request::post('role_ids', []);

        if ($userId <= 0 || empty($roleIds)) {
            Session::flash('error', 'กรุณาระบุผู้ใช้และบทบาทอย่างน้อย 1 บทบาท');
            Response::redirect('/pcc/rbac');
        }

        $db = Database::getAppDb();
        try {
            $db->beginTransaction();

            // Clear current roles
            $del = $db->prepare("DELETE FROM user_roles WHERE user_id = :id");
            $del->execute([':id' => $userId]);

            // Insert new roles
            $ins = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
            foreach ($roleIds as $rid) {
                $ins->execute([':uid' => $userId, ':rid' => (int)$rid]);
            }

            $db->commit();
            Audit::log('ROLE_ASSIGNED', 'user', (string)$userId, null, null, null, "Assigned roles: " . implode(',', $roleIds));
            Session::flash('success', 'บันทึกการกำหนดบทบาทและระดับสิทธิ์เรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('error', 'เกิดข้อผิดพลาดในการกำหนดบทบาท: ' . $e->getMessage());
        }

        Response::redirect('/pcc/rbac');
    }

    /**
     * Create New User with Role
     */
    public function createUser(): void
    {
        $username = trim(Request::post('username', ''));
        $password = Request::post('password', 'Password@123');
        $title = trim(Request::post('title', ''));
        $firstname = trim(Request::post('firstname', ''));
        $lastname = trim(Request::post('lastname', ''));
        $profession = Request::post('profession', 'pharmacist');
        $facilityId = (int)Request::post('facility_id', 1);
        $roleId = (int)Request::post('role_id', 4);
        $phone = trim(Request::post('phone', ''));

        if (empty($username) || empty($firstname) || empty($lastname)) {
            Session::flash('error', 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน (ชื่อผู้ใช้, ชื่อ, นามสกุล)');
            Response::redirect('/pcc/rbac');
        }

        $db = Database::getAppDb();

        // Check duplicate username
        $check = $db->prepare("SELECT user_id FROM users WHERE username = :u");
        $check->execute([':u' => $username]);
        if ($check->fetch()) {
            Session::flash('error', 'ชื่อผู้ใช้งาน ' . htmlspecialchars($username) . ' มีอยู่ในระบบแล้ว');
            Response::redirect('/pcc/rbac');
        }

        try {
            $db->beginTransaction();

            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("
                INSERT INTO users (facility_id, username, password_hash, title, firstname, lastname, profession, phone, is_active)
                VALUES (:fac, :u, :p, :t, :fn, :ln, :prof, :ph, 1)
            ");
            $stmt->execute([
                ':fac' => $facilityId,
                ':u' => $username,
                ':p' => $hash,
                ':t' => $title,
                ':fn' => $firstname,
                ':ln' => $lastname,
                ':prof' => $profession,
                ':ph' => $phone
            ]);
            $newUserId = (int)$db->lastInsertId();

            // Assign initial role
            $stmtRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
            $stmtRole->execute([':uid' => $newUserId, ':rid' => $roleId]);

            $db->commit();
            Audit::log('USER_CREATED', 'user', (string)$newUserId, null, null, null, "Created user {$username} with role {$roleId}");
            Session::flash('success', "สร้างบัญชีผู้ใช้งาน {$username} สำเร็จเรียบร้อยแล้ว");
        } catch (\Throwable $e) {
            $db->rollBack();
            Session::flash('error', 'เกิดข้อผิดพลาดในการสร้างบัญชี: ' . $e->getMessage());
        }

        Response::redirect('/pcc/rbac');
    }

    /**
     * Toggle User Active Status
     */
    public function toggleUserStatus(): void
    {
        $userId = (int)Request::post('user_id', 0);
        $status = (int)Request::post('status', 1);

        if ($userId <= 1) {
            Session::flash('error', 'ไม่สามารถระงับสิทธิ์บัญชีผู้ดูแลระบบหลัก (ID: 1) ได้');
            Response::redirect('/pcc/rbac');
        }

        $db = Database::getAppDb();
        $stmt = $db->prepare("UPDATE users SET is_active = :st WHERE user_id = :id");
        $stmt->execute([':st' => $status, ':id' => $userId]);

        Audit::log('USER_STATUS_CHANGE', 'user', (string)$userId, null, null, null, "Changed status to: {$status}");
        Session::flash('success', 'ปรับปรุงสถานะการใช้งานของผู้ใช้เรียบร้อยแล้ว');
        Response::redirect('/pcc/rbac');
    }

    /**
     * Persona Switcher for Quick Role Testing / Simulation
     */
    public function switchPersona(): void
    {
        $username = Request::get('username', '');
        if (empty($username)) {
            Response::redirect('/pcc/rbac');
        }

        $db = Database::getAppDb();
        $stmt = $db->prepare("SELECT user_id, username, facility_id, profession FROM users WHERE username = :u AND is_active = 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            Session::set('user_id', $user['user_id']);
            Session::set('username', $user['username']);
            Session::set('facility_id', $user['facility_id']);
            Session::set('profession', $user['profession']);

            Audit::log('PERSONA_SWITCH', 'auth', (string)$user['user_id'], null, null, null, "Switched persona to: {$username}");
            Session::flash('success', "สลับบทบาทการใช้งานเป็น: {$username} สำเร็จแล้ว");
        } else {
            Session::flash('error', "ไม่พบบัญชีผู้ใช้: {$username}");
        }

        $returnUrl = Request::get('return', '/pcc/dashboard');
        Response::redirect($returnUrl);
    }

    /**
     * Update User Master Information & Record Audit Log
     */
    public function updateUser(): void
    {
        $userId = (int)Request::post('user_id', 0);
        $title = trim(Request::post('title', ''));
        $firstname = trim(Request::post('firstname', ''));
        $lastname = trim(Request::post('lastname', ''));
        $profession = trim(Request::post('profession', 'pharmacist'));
        $licenseNo = trim(Request::post('license_number', ''));
        $facilityId = (int)Request::post('facility_id', 1);
        $phone = trim(Request::post('phone', ''));
        $password = Request::post('password', '');
        $isActive = (int)Request::post('is_active', 1);
        $roleIds = Request::post('role_ids', []);

        if ($userId <= 0 || empty($firstname) || empty($lastname)) {
            Session::flash('error', 'กรุณาระบุชื่อและนามสกุลผู้ใช้งาน');
            Response::redirect('/pcc/rbac');
            return;
        }

        $db = Database::getAppDb();
        try {
            // Fetch before payload
            $stmtBefore = $db->prepare("SELECT * FROM users WHERE user_id = :id");
            $stmtBefore->execute([':id' => $userId]);
            $before = $stmtBefore->fetch(PDO::FETCH_ASSOC);

            if (!$before) {
                Session::flash('error', 'ไม่พบบัญชีผู้ใช้งานที่ต้องการแก้ไข');
                Response::redirect('/pcc/rbac');
                return;
            }

            // Exclude password hash from before payload for safety
            $beforeLogged = $before;
            unset($beforeLogged['password_hash']);

            $db->beginTransaction();

            $sql = "UPDATE users SET 
                        title = :title, 
                        firstname = :firstname, 
                        lastname = :lastname, 
                        profession = :profession, 
                        license_number = :license, 
                        facility_id = :fac, 
                        phone = :phone, 
                        is_active = :active";
            
            $params = [
                ':title' => $title,
                ':firstname' => $firstname,
                ':lastname' => $lastname,
                ':profession' => $profession,
                ':license' => $licenseNo,
                ':fac' => $facilityId,
                ':phone' => $phone,
                ':active' => $isActive,
                ':id' => $userId
            ];

            if (!empty($password)) {
                $sql .= ", password_hash = :hash";
                $params[':hash'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $sql .= " WHERE user_id = :id";

            $stmtUpdate = $db->prepare($sql);
            $stmtUpdate->execute($params);

            // Update roles if provided
            if (!empty($roleIds)) {
                $delRoles = $db->prepare("DELETE FROM user_roles WHERE user_id = :id");
                $delRoles->execute([':id' => $userId]);

                $insRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
                foreach ($roleIds as $rid) {
                    $insRole->execute([':uid' => $userId, ':rid' => (int)$rid]);
                }
            }

            // Fetch after payload
            $stmtAfter = $db->prepare("SELECT * FROM users WHERE user_id = :id");
            $stmtAfter->execute([':id' => $userId]);
            $after = $stmtAfter->fetch(PDO::FETCH_ASSOC);
            $afterLogged = $after;
            unset($afterLogged['password_hash']);

            $db->commit();

            // Record into Audit Logs
            Audit::log(
                'USER_MASTER_UPDATED',
                'users',
                (string)$userId,
                null,
                $beforeLogged,
                $afterLogged,
                "แก้ไขข้อมูลพื้นฐานผู้ใช้งาน: {$before['username']} ({$title}{$firstname} {$lastname})"
            );

            Session::flash('success', "บันทึกการแก้ไขข้อมูลผู้ใช้งาน {$before['username']} และบันทึกประวัติ Log เรียบร้อยแล้ว");
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            Session::flash('error', 'เกิดข้อผิดพลาดในการแก้ไขข้อมูล: ' . $e->getMessage());
        }

        Response::redirect('/pcc/rbac');
    }

    /**
     * View System Audit Trail Logs (PDPA & Master Data Edit Logs)
     */
    public function auditLogs(): void
    {
        $db = Database::getAppDb();
        $module = Request::get('module', '');
        $action = Request::get('action', '');
        $search = Request::get('q', '');

        $sql = "
            SELECT a.*, u.username, u.firstname, u.lastname, u.profession
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.user_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($module)) {
            $sql .= " AND a.module_name = :mod";
            $params[':mod'] = $module;
        }
        if (!empty($action)) {
            $sql .= " AND a.action_type LIKE :act";
            $params[':act'] = "%{$action}%";
        }
        if (!empty($search)) {
            $sql .= " AND (a.reason LIKE :s1 OR a.record_id LIKE :s2 OR u.username LIKE :s3)";
            $params[':s1'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
            $params[':s3'] = "%{$search}%";
        }

        $sql .= " ORDER BY a.log_id DESC LIMIT 150";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch distinct modules for filter dropdown
        $modules = $db->query("SELECT DISTINCT module_name FROM audit_logs ORDER BY module_name ASC")->fetchAll(PDO::FETCH_COLUMN);

        View::render('rbac/audit_logs', [
            'pageTitle' => 'บันทึกประวัติการแก้ไขและตรวจสอบระบบ (System Audit Trail Logs)',
            'logs' => $logs,
            'modules' => $modules,
            'filterModule' => $module,
            'filterAction' => $action,
            'search' => $search,
            'totalCount' => count($logs)
        ]);
    }
}

