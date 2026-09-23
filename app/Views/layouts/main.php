<?php
use App\Core\Auth;
use App\Core\Session;
use App\Gateway\JhcisGateway;

$currentUser = Auth::user();
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
$isTraining = JhcisGateway::isTrainingMode();

function isActive(string $route, string $currentUri): string {
    $parsed = parse_url($currentUri, PHP_URL_PATH);
    if ($route === '/hos/dashboard' && ($parsed === '/hos' || $parsed === '/hos/' || $parsed === '/hos/dashboard')) {
        return 'active';
    }
    return str_starts_with($parsed, $route) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'PCU Smart Pharmacy') ?> — ระบบบริหารจัดการระบบยาปฐมภูมิ</title>
    <link rel="stylesheet" href="/hos/assets/css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💊</text></svg>">
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
            <div class="menu-category">ระบบบริการคลินิก & ผู้ป่วย</div>
            <a href="/hos/dashboard" class="nav-item <?= isActive('/hos/dashboard', $currentUri) ?>">
                <span class="nav-icon">📊</span>
                <span>แดชบอร์ดบริหารระบบยา</span>
            </a>
            <a href="/hos/patients" class="nav-item <?= isActive('/hos/patients', $currentUri) ?>">
                <span class="nav-icon">👤</span>
                <span>ค้นหาผู้รับบริการ & แฟ้มยา</span>
            </a>
            <a href="/hos/reviews" class="nav-item <?= isActive('/hos/reviews', $currentUri) ?>">
                <span class="nav-icon">📋</span>
                <span>Medication Review & DRP</span>
            </a>

            <div class="menu-category">คลังยา & ความปลอดภัย</div>
            <a href="/hos/inventory" class="nav-item <?= isActive('/hos/inventory', $currentUri) ?>">
                <span class="nav-icon">📦</span>
                <span>คลังยา FEFO & วันหมดอายุ</span>
            </a>
            <a href="/hos/inventory/movements" class="nav-item <?= isActive('/hos/inventory/movements', $currentUri) ?>">
                <span class="nav-icon">📑</span>
                <span>Stock Card เคลื่อนไหว</span>
            </a>
            <a href="/hos/cold-chain" class="nav-item <?= isActive('/hos/cold-chain', $currentUri) ?>">
                <span class="nav-icon">❄️</span>
                <span>ตู้เย็นยา Cold Chain (2-8°C)</span>
            </a>
            <a href="/hos/safety/ham-lasa" class="nav-item <?= isActive('/hos/safety/ham-lasa', $currentUri) ?>">
                <span class="nav-icon">⚠️</span>
                <span>ยาความเสี่ยงสูง (HAM / LASA)</span>
            </a>
            <a href="/hos/safety/emergency-kit" class="nav-item <?= isActive('/hos/safety/emergency-kit', $currentUri) ?>">
                <span class="nav-icon">🚑</span>
                <span>ชุดยาช่วยชีวิต CPR Kit</span>
            </a>

            <div class="menu-category">มาตรฐานปฐมภูมิ & คุณภาพ</div>
            <a href="/hos/quality" class="nav-item <?= isActive('/hos/quality', $currentUri) ?>">
                <span class="nav-icon">🏅</span>
                <span>มาตรฐานปฐมภูมิ 2568–70</span>
            </a>
            <a href="/hos/quality/evidence" class="nav-item <?= isActive('/hos/quality/evidence', $currentUri) ?>">
                <span class="nav-icon">📁</span>
                <span>ศูนย์หลักฐานเชิงประจักษ์</span>
            </a>
            <a href="/hos/kpi" class="nav-item <?= isActive('/hos/kpi', $currentUri) ?>">
                <span class="nav-icon">📈</span>
                <span>ตัวชี้วัดคุณภาพ (KPIs)</span>
            </a>
            <a href="/hos/kpi/rdu" class="nav-item <?= isActive('/hos/kpi/rdu', $currentUri) ?>">
                <span class="nav-icon">🌱</span>
                <span>RDU & การใช้ยาปฏิชีวนะ</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-snippet">
                <div class="user-avatar">
                    <?= htmlspecialchars(mb_substr($currentUser['firstname'] ?? 'U', 0, 1, 'UTF-8')) ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars(($currentUser['title'] ?? '') . ($currentUser['firstname'] ?? 'ผู้ใช้งาน') . ' ' . ($currentUser['lastname'] ?? '')) ?></div>
                    <div class="user-role"><?= htmlspecialchars($currentUser['roles'][0]['display_name'] ?? 'เจ้าหน้าที่') ?></div>
                </div>
                <a href="/hos/logout" title="ออกจากระบบ" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 13px;">🚪</a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="app-main">
        <header class="app-header">
            <div class="header-left">
                <h2 class="header-title"><?= htmlspecialchars($pageTitle ?? 'PCU Smart Pharmacy') ?></h2>
            </div>
            <div class="header-right">
                <!-- Facility Tag -->
                <span class="badge badge-secondary" style="font-size: 13px; padding: 6px 12px;">
                    🏥 <?= htmlspecialchars($currentUser['facility_name'] ?? 'รพ.สต.บ้านหนองบัว') ?> (รหัส: <?= htmlspecialchars($currentUser['facility_code'] ?? '05432') ?>)
                </span>

                <!-- Gateway Status -->
                <div class="gateway-badge <?= $isTraining ? 'training' : '' ?>" title="JHCIS Connection on Port 3333">
                    <span class="status-dot"></span>
                    <span>JHCIS <?= $isTraining ? 'จำลองเคสคลินิก (Training Mode)' : 'เชื่อมต่อสด (Port 3333)' ?></span>
                </div>
            </div>
        </header>

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
</script>
</body>
</html>
