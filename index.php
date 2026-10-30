<?php
session_start();
require __DIR__ . '/config/db.php';
require __DIR__ . '/config/lookups.php';
require __DIR__ . '/admin/auth.php';

// หน้าที่อนุญาต (whitelist)
$allowed = ['air_evac_form', 'air_evac_list', 'login', 'admin_dashboard', 'admin_form', 'air_evac_detail', 'air_evac_edit'];
$page = $_GET['page'] ?? 'air_evac_form';

// ตัวรับข้อมูล POST: ต้องทำงานก่อนมี output ใดๆ เพื่อ redirect ได้
if ($page === 'logout') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'คำขอออกจากระบบไม่ถูกต้อง'];
        header('Location: index.php?page=' . (admin_logged_in() ? 'admin_dashboard' : 'login'));
        exit;
    }

    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    header('Location: index.php');
    exit;
}

if ($page === 'air_evac_save') {
    require __DIR__ . '/pages/air_evac_save.php';
    exit;
}

if ($page === 'air_evac_update') {
    require_admin();
    require __DIR__ . '/pages/air_evac_update.php';
    exit;
}

if (!in_array($page, $allowed, true)) {
    $page = 'air_evac_form';
}

if ($page === 'admin_dashboard' || $page === 'admin_form' || $page === 'air_evac_detail' || $page === 'air_evac_edit'
    || ($page === 'air_evac_list' && admin_logged_in())) {
    require_admin();
    include __DIR__ . '/includes/admin_header.php';
    if ($page === 'admin_dashboard') {
        include __DIR__ . '/pages/dashboard.php';
    } elseif ($page === 'admin_form') {
        include __DIR__ . '/pages/air_evac_form.php';
    } elseif ($page === 'air_evac_detail') {
        include __DIR__ . '/pages/air_evac_detail.php';
    } elseif ($page === 'air_evac_edit') {
        include __DIR__ . '/pages/air_evac_edit.php';
    } else {
        include __DIR__ . '/pages/air_evac_list.php';
    }
    include __DIR__ . '/includes/admin_footer.php';
    exit;
}

if ($page === 'login') {
    include __DIR__ . '/pages/login.php';
    exit;
}

include __DIR__ . '/includes/header.php';
include __DIR__ . "/pages/{$page}.php";
include __DIR__ . '/includes/footer.php';
