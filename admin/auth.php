<?php
// ตัวช่วยเกี่ยวกับการยืนยันตัวตนของแอดมิน
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
 
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
 
function admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}
 
// เรียกบรรทัดแรกของทุกหน้าแอดมิน และทุกตัวรับ POST (แก้ไข/ลบ)
function require_admin(): void {
    if (!admin_logged_in()) {
        header('Location: index.php?page=login');
        exit;
    }
}
 
function csrf_ok(): bool {
    $s = $_SESSION['csrf'] ?? '';
    $p = $_POST['csrf'] ?? '';
    return is_string($s) && is_string($p) && hash_equals($s, $p);
}