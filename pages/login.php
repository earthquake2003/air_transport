<?php
// หน้าเข้าสู่ระบบแอดมิน — ถูกเรียกจาก index.php?page=login ก่อนมี output จึง redirect ได้
// (หน้านี้เป็น HTML เต็มหน้าเอง ไม่ใช้ header/footer ของเว็บ)
if (admin_logged_in()) {
    header('Location: index.php?page=admin_dashboard');
    exit;
}

$error  = '';
$locked = ($_SESSION['login_lock'] ?? 0) > time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        $error = 'ลองผิดหลายครั้ง กรุณารอ 5 นาทีแล้วลองใหม่';
    } elseif (!csrf_ok()) {
        $error = 'คำขอไม่ถูกต้อง กรุณารีเฟรชหน้าแล้วลองใหม่';
    } else {
        $u = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
        $p = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :u AND is_active = 1');
        $stmt->execute([':u' => $u]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($p, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['csrf']       = bin2hex(random_bytes(32));
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            unset($_SESSION['login_fail'], $_SESSION['login_lock']);
            $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = :id')
                ->execute([':id' => $admin['id']]);
            header('Location: index.php?page=admin_dashboard');
            exit;
        }

        $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
        if ($_SESSION['login_fail'] >= 5) {
            $_SESSION['login_lock'] = time() + 300;
            $_SESSION['login_fail'] = 0;
        }
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>เข้าสู่ระบบแอดมิน</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-login">
  <main class="login-shell">
    <section class="login-brand-panel">
      <div class="login-brand-mark"><i class="bi bi-airplane-engines" aria-hidden="true"></i></div>
      <div class="login-brand-copy">
        <span class="login-eyebrow">AIR EVACUATION MANAGEMENT</span>
        <h1>ระบบลำเลียง<br>ผู้ป่วยทางอากาศ</h1>
        <p>ระบบจัดการข้อมูลสำหรับเจ้าหน้าที่และผู้ดูแลระบบ</p>
      </div>
      <div class="login-panel-decoration" aria-hidden="true"><i class="bi bi-plus-lg"></i></div>
    </section>
    <section class="login-form-panel">
      <div class="login-form-content">
        <div class="login-mobile-mark"><i class="bi bi-airplane-engines" aria-hidden="true"></i></div>
        <span class="login-eyebrow text-primary">ADMINISTRATOR ACCESS</span>
        <h2>เข้าสู่ระบบหลังบ้าน</h2>
        <p class="text-muted mb-4">กรุณากรอกบัญชีผู้ดูแลระบบเพื่อดำเนินการต่อ</p>

        <?php if ($flash): ?>
          <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : 'warning') ?> py-2" role="status">
            <?= e($flash['msg']) ?>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span><?= e($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="post" action="index.php?page=login" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
          <div class="mb-3">
            <label class="form-label" for="username">ชื่อผู้ใช้</label>
            <div class="input-group login-input-group">
              <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
              <input type="text" id="username" name="username" class="form-control"
                     autocomplete="username" required autofocus>
            </div>
          </div>
          <div class="mb-4">
            <label class="form-label" for="password">รหัสผ่าน</label>
            <div class="input-group login-input-group">
              <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
              <input type="password" id="password" name="password" class="form-control"
                     autocomplete="current-password" required>
            </div>
          </div>
          <button type="submit" class="btn btn-login w-100">
            เข้าสู่ระบบ <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
          </button>
        </form>
        <div class="login-secure-note"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>สำหรับผู้ดูแลระบบที่ได้รับอนุญาตเท่านั้น</div>
        <a href="index.php?page=air_evac_form" class="login-back-link">
          <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับหน้าบันทึกข้อมูล
        </a>
      </div>
    </section>
  </main>
</body>
</html>