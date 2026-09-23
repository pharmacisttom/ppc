<?php
use App\Core\Auth;
use App\Core\Session;
use App\Gateway\JhcisGateway;

$currentUser = Auth::user();
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';

if (!function_exists('isActive')) {
    function isActive(string $route, string $currentUri): string {
        $parsed = parse_url($currentUri, PHP_URL_PATH);
        if ($route === '/pcc/dashboard' && ($parsed === '/pcc' || $parsed === '/pcc/' || $parsed === '/pcc/dashboard' || $parsed === '/hos' || $parsed === '/hos/' || $parsed === '/hos/dashboard')) {
            return 'active';
        }
        return str_starts_with($parsed, $route) ? 'active' : '';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'PCU Smart Pharmacy') ?> — ระบบบริหารจัดการระบบยาปฐมภูมิ</title>
    <link rel="stylesheet" href="/pcc/assets/css/style.css?v=<?= time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💊</text></svg>">
    <style>
        /* Signature BHPCU Royal Purple Header & Search Subbar */
        .pcu-topbar {
            background: #4a154b;
            color: #ffffff;
            height: 54px;
            padding: 0 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(74, 21, 75, 0.2);
        }
        .pcu-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-sidebar-toggle {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #ffffff;
            border-radius: 6px;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .pcu-brand-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .pcu-facility-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .pcu-facility-title {
            font-size: 15.5px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.2px;
        }
        .pcu-mode-badge {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 14px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .pcu-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pcu-nav-btn {
            color: #f3e8ff;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .pcu-nav-btn:hover, .pcu-nav-btn.active {
            background: #ffffff;
            color: #4a154b;
            font-weight: 700;
        }
        .pcu-logout-btn {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fecaca;
        }
        .pcu-logout-btn:hover {
            background: #ef4444;
            color: #ffffff;
        }
        /* Subbar */
        .pcu-subbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .pcu-search-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            max-width: 960px;
        }
        .pcu-search-input {
            flex: 1;
            height: 38px;
            border: 1px solid #d8b4fe;
            border-radius: 6px;
            padding: 0 14px;
            font-size: 14px;
            background: #ffffff;
            color: #1e1b4b;
            outline: none;
            transition: all 0.2s;
        }
        .pcu-search-input:focus {
            border-color: #9333ea;
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.12);
        }
        .pcu-btn-search {
            background: #4a154b;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            height: 38px;
            padding: 0 24px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .pcu-btn-search:hover {
            background: #3b0764;
        }
        .pcu-btn-mederror {
            background: #ef4444;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            height: 38px;
            padding: 0 18px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.25);
            transition: all 0.2s;
        }
        .pcu-btn-mederror:hover {
            background: #dc2626;
            color: #ffffff;
        }
        /* Collapsible Sidebar Support */
        .app-container.sidebar-collapsed .app-sidebar {
            transform: translateX(-100%);
            transition: transform 0.25s ease-in-out;
        }
        .app-container.sidebar-collapsed .app-main {
            margin-left: 0;
            transition: margin-left 0.25s ease-in-out;
        }
        .app-sidebar {
            transition: transform 0.25s ease-in-out;
        }
        .app-main {
            transition: margin-left 0.25s ease-in-out;
            background: #f8fafc;
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar -->
    <aside class="app-sidebar">
        <div class="sidebar-header">
            <div class="logo-badge">💊</div>
            <div class="brand-text">
                <h1>Smart Pharmacy</h1>
                <span>PCU Clinical Care v1.0</span>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-category">📊 สารสนเทศ & แดชบอร์ดปฐมภูมิ</div>
            <a href="/pcc/dashboard" class="nav-item <?= isActive('/pcc/dashboard', $currentUri) ?>">
                <span class="nav-icon">📊</span>
                <span>แดชบอร์ดสารสนเทศ & กราฟสถิติ</span>
            </a>
            <a href="/pcc/vhv" class="nav-item <?= (isActive('/pcc/vhv', $currentUri) || isActive('/pcc/safety/vhv', $currentUri)) ? 'active' : '' ?>">
                <span class="nav-icon">👥</span>
                <span>ทำเนียบ อสม. ในพื้นที่ (JHCIS)</span>
            </a>
            <a href="/pcc/patients" class="nav-item <?= isActive('/pcc/patients', $currentUri) ?>">
                <span class="nav-icon">👤</span>
                <span>ค้นหาผู้รับบริการ (JHCIS)</span>
            </a>
            <a href="/pcc/reviews" class="nav-item <?= isActive('/pcc/reviews', $currentUri) ?>">
                <span class="nav-icon">📋</span>
                <span>Medication Review & DRP</span>
            </a>

            <div class="menu-category">🛡️ ความปลอดภัยด้านยา & ทะเบียนกลุ่มเสี่ยง</div>
            <a href="/pcc/safety" class="nav-item <?= ($currentUri === '/pcc/safety' || $currentUri === '/pcc/safety/') ? 'active' : '' ?>">
                <span class="nav-icon">🛡️</span>
                <span>ศูนย์บัญชาการความปลอดภัย</span>
            </a>
            <a href="/pcc/safety/allergies" class="nav-item <?= (str_starts_with($currentUri, '/pcc/safety/allergies')) ? 'active' : '' ?>">
                <span class="nav-icon">🚫</span>
                <span>ทะเบียนผู้ป่วยแพ้ยา (Allergy)</span>
            </a>
            <a href="/pcc/safety/g6pd" class="nav-item <?= isActive('/pcc/safety/g6pd', $currentUri) ?>">
                <span class="nav-icon">🧬</span>
                <span>ผู้ป่วยพร่องเอนไซม์ G6PD</span>
            </a>
            <a href="/pcc/safety/warfarin" class="nav-item <?= isActive('/pcc/safety/warfarin', $currentUri) ?>">
                <span class="nav-icon">🩸</span>
                <span>ผู้ป่วยรับยา Warfarin</span>
            </a>
            <a href="/pcc/safety/anticonvulsant" class="nav-item <?= isActive('/pcc/safety/anticonvulsant', $currentUri) ?>">
                <span class="nav-icon">🧠</span>
                <span>ผู้รับยากันชัก (Anticonvulsant)</span>
            </a>
            <a href="/pcc/safety/ckd" class="nav-item <?= isActive('/pcc/safety/ckd', $currentUri) ?>">
                <span class="nav-icon">🫘</span>
                <span>คลินิกโรคไตเรื้อรัง (CKD Care)</span>
            </a>
            <a href="/pcc/safety/incidents" class="nav-item <?= isActive('/pcc/safety/incidents', $currentUri) ?>">
                <span class="nav-icon">🚨</span>
                <span>อุบัติการณ์ทางยา & RCA</span>
            </a>
            <a href="/pcc/safety/ham-lasa" class="nav-item <?= isActive('/pcc/safety/ham-lasa', $currentUri) ?>">
                <span class="nav-icon">⚠️</span>
                <span>ยาความเสี่ยงสูง (HAM / LASA)</span>
            </a>
            <a href="/pcc/safety/emergency-kit" class="nav-item <?= isActive('/pcc/safety/emergency-kit', $currentUri) ?>">
                <span class="nav-icon">🚑</span>
                <span>ชุดยาช่วยชีวิต CPR Kit</span>
            </a>

            <div class="menu-category">🌐 แลกเปลี่ยนข้อมูล รพ.แม่ข่าย (CUP Exchange)</div>
            <a href="/pcc/exchange" class="nav-item <?= isActive('/pcc/exchange', $currentUri) ?>">
                <span class="nav-icon">📂</span>
                <span>ศูนย์ส่งออก 43 แฟ้ม สธ.</span>
            </a>
            <a href="/pcc/exchange/file/DRUG_OPD" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>แฟ้มจ่ายยา (DRUG_OPD)</span>
            </a>
            <a href="/pcc/exchange/file/DRUG_ALLERGY" class="nav-item">
                <span class="nav-icon">🚫</span>
                <span>แฟ้มประวัติแพ้ยา (ALLERGY)</span>
            </a>
            <a href="/pcc/exchange/file/CHRONIC" class="nav-item">
                <span class="nav-icon">📋</span>
                <span>แฟ้มโรคเรื้อรัง (CHRONIC)</span>
            </a>
            <a href="/pcc/exchange/file/LABFU" class="nav-item">
                <span class="nav-icon">🔬</span>
                <span>แฟ้มผลแล็บ (LABFU INR/Cr)</span>
            </a>
            <a href="/pcc/exchange/excel" class="nav-item">
                <span class="nav-icon">📁</span>
                <span>ส่งออก Excel เชื่อมโยง CUP</span>
            </a>

            <div class="menu-category">📦 คลังเวชภัณฑ์ & ควบคุมยา</div>
            <a href="/pcc/drugs" class="nav-item <?= isActive('/pcc/drugs', $currentUri) ?>">
                <span class="nav-icon">💊</span>
                <span>บัญชีรายการยา รพ.สต.</span>
            </a>
            <a href="/pcc/inventory/jhcis-stores" class="nav-item <?= isActive('/pcc/inventory/jhcis-stores', $currentUri) ?>">
                <span class="nav-icon">🏢</span>
                <span>คลังยานอก-ใน JHCIS</span>
            </a>
            <a href="/pcc/inventory/rb301" class="nav-item <?= isActive('/pcc/inventory/rb301', $currentUri) ?>">
                <span class="nav-icon">📋</span>
                <span>บัญชีคุมเวชภัณฑ์ (รบ. 301)</span>
            </a>
            <a href="/pcc/inventory" class="nav-item <?= isActive('/pcc/inventory', $currentUri) ?>">
                <span class="nav-icon">📦</span>
                <span>คลังยา FEFO & วันหมดอายุ</span>
            </a>
            <a href="/pcc/inventory/movements" class="nav-item <?= isActive('/pcc/inventory/movements', $currentUri) ?>">
                <span class="nav-icon">📑</span>
                <span>Stock Card เคลื่อนไหว</span>
            </a>
            <a href="/pcc/cold-chain" class="nav-item <?= isActive('/pcc/cold-chain', $currentUri) ?>">
                <span class="nav-icon">❄️</span>
                <span>ตู้เย็นยา Cold Chain (2-8°C)</span>
            </a>

            <div class="menu-category">🏅 มาตรฐานปฐมภูมิ & คุณภาพ</div>
            <a href="/pcc/reports" class="nav-item <?= isActive('/pcc/reports', $currentUri) ?>">
                <span class="nav-icon">📊</span>
                <span>ศูนย์รายงาน & ส่งออก Excel</span>
            </a>
            <a href="/pcc/quality" class="nav-item <?= isActive('/pcc/quality', $currentUri) ?>">
                <span class="nav-icon">🏅</span>
                <span>มาตรฐานปฐมภูมิ 2568–70</span>
            </a>
            <a href="/pcc/quality/evidence" class="nav-item <?= isActive('/pcc/quality/evidence', $currentUri) ?>">
                <span class="nav-icon">📁</span>
                <span>ศูนย์หลักฐานเชิงประจักษ์</span>
            </a>
            <a href="/pcc/kpi" class="nav-item <?= isActive('/pcc/kpi', $currentUri) ?>">
                <span class="nav-icon">📈</span>
                <span>ตัวชี้วัดคุณภาพ (KPIs)</span>
            </a>
            <a href="/pcc/kpi/rdu" class="nav-item <?= isActive('/pcc/kpi/rdu', $currentUri) ?>">
                <span class="nav-icon">🌱</span>
                <span>RDU & การใช้ยาปฏิชีวนะ</span>
            </a>

            <div class="menu-category">⚙️ การบริหารระบบ & ความปลอดภัย</div>
            <a href="/pcc/settings" class="nav-item <?= isActive('/pcc/settings', $currentUri) ?>">
                <span class="nav-icon">⚙️</span>
                <span>ตั้งค่าข้อมูลพื้นฐานอัจฉริยะ</span>
            </a>
            <a href="/pcc/rbac" class="nav-item <?= isActive('/pcc/rbac', $currentUri) ?>">
                <span class="nav-icon">👥</span>
                <span>ระดับสิทธิ์ & ผู้ใช้งาน (RBAC)</span>
            </a>
            <a href="/pcc/audit-logs" class="nav-item <?= isActive('/pcc/audit-logs', $currentUri) ?>">
                <span class="nav-icon">📜</span>
                <span>บันทึกประวัติแก้ไขระบบ (Audit Logs)</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-snippet">
                <div class="user-avatar">
                    <?= htmlspecialchars(mb_substr($currentUser['firstname'] ?? 'U', 0, 1, 'UTF-8')) ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars(($currentUser['title'] ?? '') . ($currentUser['firstname'] ?? 'ผู้ใช้งาน') . ' ' . ($currentUser['lastname'] ?? '')) ?></div>
                    <div class="user-role" style="display: flex; align-items: center; gap: 4px;">
                        <span><?= htmlspecialchars($currentUser['roles'][0]['display_name'] ?? 'เจ้าหน้าที่') ?></span>
                        <span class="badge badge-secondary" style="font-size: 9px; padding: 1px 4px; background: rgba(255,255,255,0.15); color: #93c5fd;">
                            L<?= Auth::accessLevel() ?>
                        </span>
                    </div>
                </div>
                <a href="/pcc/logout" title="ออกจากระบบ" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 13px;">🚪</a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="app-main">
        <!-- BHPCU Signature Royal Purple Topbar -->
        <header class="pcu-topbar">
            <div class="pcu-topbar-left">
                <button type="button" class="btn-sidebar-toggle" onclick="toggleSidebar()" title="ย่อ/ขยายเมนูด้านข้าง">
                    ☰
                </button>
                <div class="pcu-brand-circle">
                    💊
                </div>
                <div class="pcu-facility-info">
                    <span class="pcu-facility-title">
                        <?= htmlspecialchars($currentUser['facility_name'] ?? 'รพ.สต.บ้านดอกกราย') ?> ต.แม่น้ำคู้ อ.ปลวกแดง จ.ระยอง
                    </span>
                    <a href="/pcc/settings" class="pcu-mode-badge" style="text-decoration: none; background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;" title="คลิกเพื่อตั้งค่าหน่วยบริการ">
                        📊 ระบบ: สารสนเทศ & ความปลอดภัยด้านยา
                    </a>
                </div>
            </div>

            <div class="pcu-topbar-right">
                <a href="/pcc/dashboard" class="pcu-nav-btn <?= ($currentUri === '/pcc/dashboard' || $currentUri === '/hos' || $currentUri === '/pcc/') ? 'active' : '' ?>">
                    <span>📊</span> <span>Dashboard</span>
                </a>
                <a href="/pcc/safety" class="pcu-nav-btn <?= (str_starts_with($currentUri, '/pcc/safety')) ? 'active' : '' ?>">
                    <span>🛡️</span> <span>Safety</span>
                </a>
                <a href="/pcc/exchange" class="pcu-nav-btn <?= (str_starts_with($currentUri, '/pcc/exchange')) ? 'active' : '' ?>">
                    <span>🌐</span> <span>43 แฟ้ม สธ.</span>
                </a>
                <a href="/pcc/inventory/jhcis-stores" class="pcu-nav-btn <?= (str_starts_with($currentUri, '/pcc/inventory')) ? 'active' : '' ?>">
                    <span>📦</span> <span>คลังยา</span>
                </a>
                <a href="/pcc/settings" class="pcu-nav-btn <?= (str_starts_with($currentUri, '/pcc/settings')) ? 'active' : '' ?>">
                    <span>⚙️</span> <span>ตั้งค่า</span>
                </a>
                <a href="/pcc/logout" class="pcu-nav-btn pcu-logout-btn" title="ออกจากระบบ">
                    <span>🚪</span> <span>ออก (<?= htmlspecialchars($currentUser['username'] ?? 'adm') ?>)</span>
                </a>
            </div>
        </header>

        <!-- Subheader: Universal Patient Safety Search & Mederror -->
        <div class="pcu-subbar">
            <form action="/pcc/patients" method="GET" class="pcu-search-form">
                <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="🔍 ค้นหาผู้รับบริการ (PID, เลขบัตร 13 หลัก, ชื่อ-สกุล) เพื่อดูประวัติแพ้ยา, G6PD, NCD..." class="pcu-search-input">
                <button type="submit" class="pcu-btn-search">
                    ค้นหาผู้ป่วย
                </button>
            </form>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="gateway-badge" style="margin: 0; padding: 5px 10px;" title="JHCIS Live Connection on Port 3333">
                    <span class="status-dot"></span>
                    <span style="font-size: 11.5px;">JHCIS 3333 สด</span>
                </div>
                <a href="/pcc/safety/incidents/create" class="pcu-btn-mederror" title="รายงานอุบัติการณ์และความคลาดเคลื่อนทางยา (Near Miss / Mederror)">
                    <span>⚠️</span> Mederror
                </a>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if ($successMsg = Session::flash('success')): ?>
                <div class="alert alert-success">
                    <span>✅</span>
                    <span><?= htmlspecialchars($successMsg) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($errorMsg = Session::flash('error')): ?>
                <div class="alert alert-error">
                    <span>⚠️</span>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($infoMsg = Session::flash('info')): ?>
                <div class="alert alert-info" style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #3b82f6; color: #1e40af; padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
                    <span>ℹ️</span>
                    <span><?= htmlspecialchars($infoMsg) ?></span>
                </div>
            <?php endif; ?>

            <!-- View Specific Content -->
            <?= $content ?? '' ?>
        </div>
    </main>
</div>

<script>
// Global helper for opening/closing modals
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal-backdrop')) {
        event.target.style.display = 'none';
    }
};

// Sidebar Toggle Function with LocalStorage Memory
function toggleSidebar() {
    const container = document.querySelector('.app-container');
    if (container) {
        container.classList.toggle('sidebar-collapsed');
        const isCollapsed = container.classList.contains('sidebar-collapsed');
        localStorage.setItem('pcu_sidebar_collapsed', isCollapsed ? '1' : '0');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const path = window.location.pathname;
    const isDispense = path.includes('/dispense');
    const saved = localStorage.getItem('pcu_sidebar_collapsed');
    const container = document.querySelector('.app-container');
    if (container) {
        if (saved === '1' || (saved === null && isDispense)) {
            container.classList.add('sidebar-collapsed');
        }
    }
});
</script>
</body>
</html>
