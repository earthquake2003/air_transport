<?php
// รับข้อมูลจากฟอร์ม -> ตรวจสอบ -> INSERT -> redirect กลับ
$formPage = admin_logged_in() ? 'admin_form' : 'air_evac_form';
$sessionCsrf = $_SESSION['csrf'] ?? '';
$postedCsrf = $_POST['csrf'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !is_string($sessionCsrf)
    || !is_string($postedCsrf)
    || !hash_equals($sessionCsrf, $postedCsrf)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'คำขอไม่ถูกต้อง'];
    header('Location: index.php?page=' . $formPage);
    exit;
}

$in = fn($k) => is_string($_POST[$k] ?? null) ? trim($_POST[$k]) : '';
$data = [
    'date'          => $in('date'),
    'hn'            => $in('hn'),
    'an'            => $in('an'),
    'rank_code'     => $in('rank_code') ?: null,
    'first_name'    => $in('first_name'),
    'last_name'     => $in('last_name'),
    'unit'          => $in('unit') ?: null,
    'mission_type'  => (int)$in('mission_type'),
    'mission_name'  => $in('mission_name') ?: null,
    'origin'        => $in('origin'),
    'destination'   => $in('destination'),
    'diagnosis'     => $in('diagnosis'),
    'aircraft_type' => (int)$in('aircraft_type'),
    'remark_type'   => (int)$in('remark_type'),
];

// ตรวจสอบ
$errors = [];
$d = DateTime::createFromFormat('Y-m-d', $data['date']);
if (!$d || $d->format('Y-m-d') !== $data['date']) $errors[] = 'วันที่ไม่ถูกต้อง';
foreach (['hn' => 'HN', 'an' => 'AN', 'first_name' => 'ชื่อ', 'last_name' => 'นามสกุล',
          'origin' => 'ต้นทาง', 'destination' => 'ปลายทาง', 'diagnosis' => 'วินิจฉัยเบื้องต้น'] as $k => $label) {
    if ($data[$k] === '') $errors[] = "กรุณากรอก{$label}";
}
if (!isset($MISSIONS[$data['mission_type']]))    $errors[] = 'กรุณาเลือกภารกิจ';
if (!isset($AIRCRAFTS[$data['aircraft_type']]))  $errors[] = 'กรุณาเลือกชนิดอากาศยาน';
if (!isset($REMARKS[$data['remark_type']]))      $errors[] = 'กรุณาเลือกหมายเหตุ';
if ($errors) {
    $_SESSION['old']   = array_filter($_POST, 'is_string');
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => implode(' / ', $errors)];
    header('Location: index.php?page=' . $formPage);
    exit;
}

try {
    $sql = "INSERT INTO air_patients
              (`date`, hn, an, rank_code, first_name, last_name, unit, mission_type,
               mission_name, origin, destination, diagnosis, aircraft_type, remark_type)
            VALUES
              (:date, :hn, :an, :rank_code, :first_name, :last_name, :unit, :mission_type,
               :mission_name, :origin, :destination, :diagnosis, :aircraft_type, :remark_type)";
    $pdo->prepare($sql)->execute($data);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'บันทึกข้อมูลสำเร็จ'];
    header('Location: index.php?page=air_evac_list');
} catch (PDOException $e) {
    error_log('Air evacuation patient save failed: ' . $e->getMessage());
    $_SESSION['old']   = array_filter($_POST, 'is_string');
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'บันทึกไม่สำเร็จ'];
    header('Location: index.php?page=' . $formPage);
}
exit;