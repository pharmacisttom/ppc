<?php
use App\Core\CSRF;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'เข้าสู่ระบบ') ?> — PCU Smart Pharmacy</title>
    <link rel="stylesheet" href="/hos/assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .login-header {
            background: var(--primary-gradient);
            padding: 32px 28px 24px;
            color: #ffffff;
            text-align: center;
        }
        .login-logo {
            font-size: 42px;
            margin-bottom: 12px;
            display: inline-block;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
        }
        .login-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }
        .login-header p {
            font-size: 13px;
            color: var(--primary-light);
            font-weight: 400;
        }
        .login-body {
            padding: 28px;
        }
        .demo-roles {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        .demo-roles h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 10px;
            text-align: center;
        }
        .demo-btn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .demo-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            cursor: pointer;
            text-align: left;
            transition: all 0.15s ease;
        }
        .demo-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary-dark);
        }
        .demo-btn strong {
            display: block;
            font-size: 12px;
            color: var(--text-primary);
        }
        .demo-btn span {
            font-size: 11px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="login-logo">💊</div>
        <h1>PCU Smart Pharmacy</h1>
        <p>ระบบบริหารจัดการด้านยาและบริบาลเภสัชกรรมปฐมภูมิ</p>
    </div>

    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="margin-bottom: 16px; font-size: 13px; padding: 10px 14px;">
                <span>⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 16px; font-size: 13px; padding: 10px 14px;">
                <span>✅</span>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form action="/hos/login" method="POST" id="loginForm">
            <?= CSRF::field() ?>
            <div class="form-group">
                <label class="form-label" for="username">ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="เช่น pcu.pharm หรือ admin" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="password">รหัสผ่าน (Password)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-size: 15px; font-weight: 600;">
                เข้าสู่ระบบ 🚀
            </button>
        </form>

        <div class="demo-roles">
            <h4>⚡ บัญชีทดสอบระบบ (คลิกเพื่อเข้าใช้งานทันที)</h4>
            <div class="demo-btn-grid">
                <button type="button" class="demo-btn" onclick="fillLogin('pcu.pharm', 'Password@123')">
                    <strong>ภญ.กานดา (เภสัชกร)</strong>
                    <span>บทบาท เภสัชกรปฐมภูมิ</span>
                </button>
                <button type="button" class="demo-btn" onclick="fillLogin('nurse.somjai', 'Password@123')">
                    <strong>พว.สมใจ (พยาบาล)</strong>
                    <span>บทบาท พยาบาลวิชาชีพ</span>
                </button>
                <button type="button" class="demo-btn" onclick="fillLogin('tech.wirat', 'Password@123')">
                    <strong>นายวิรัช (จพ.เภสัช)</strong>
                    <span>บทบาท เจ้าพนักงานยา</span>
                </button>
                <button type="button" class="demo-btn" onclick="fillLogin('admin', 'Password@123')">
                    <strong>ภก.สุรศักดิ์ (Admin)</strong>
                    <span>บทบาท ผู้ดูแลระบบสูงสุด</span>
                </button>
            </div>
        </div>

        <p style="text-align: center; font-size: 11px; color: var(--text-muted); margin-top: 18px;">
            🔒 รองรับการเข้ารหัสข้อมูลตามมาตรฐาน PDPA & JHCIS Isolation Gateway
        </p>
    </div>
</div>

<script>
function fillLogin(u, p) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = p;
    document.getElementById('loginForm').submit();
}
</script>

</body>
</html>
