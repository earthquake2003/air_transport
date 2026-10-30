<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'คำขอไม่ถูกต้อง กรุณาลองใหม่'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$rawId = $_POST['id'] ?? '';
$id = is_string($rawId) && ctype_digit($rawId) ? (int)$rawId : 0;
if ($id < 1) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ไม่พบรายการผู้ป่วยที่ต้องการแก้ไข'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$exists = $pdo->prepare('SELECT id FROM air_patients WHERE id = :id');
$exists->execute([':id' => $id]);
if (!$exists->fetchColumn()) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ไม่พบรายการผู้ป่วยที่ต้องการแก้ไข'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$input = static fn($key) => is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
$missionType = filter_var($input('mission_type'), FILTER_VALIDATE_INT);
$aircraftType = filter_var($input('aircraft_type'), FILTER_VALIDATE_INT);
$remarkType = filter_var($input('remark_type'), FILTER_VALIDATE_INT);
$data = [
    'date' => $input('date'),
    'hn' => $input('hn'),
    'an' => $input('an'),
    'rank_code' => $input('rank_code') ?: null,
    'first_name' => $input('first_name'),
    'last_name' => $input('last_name'),
    'unit' => $input('unit') ?: null,
    'mission_type' => $missionType === false ? 0 : $missionType,
    'mission_name' => $input('mission_name') ?: null,
    'origin' => $input('origin'),
    'destination' => $input('destination'),
    'diagnosis' => $input('diagnosis'),
    'aircraft_type' => $aircraftType === false ? 0 : $aircraftType,
    'remark_type' => $remarkType === false ? 0 : $remarkType,
];

$errors = [];
$date = DateTime::createFromFormat('!Y-m-d', $data['date']);
if (!$date || $date->format('Y-m-d') !== $data['date']) {
    $errors[] = 'วันที่ไม่ถูกต้อง';
}
foreach ([
    'hn' => 'HN',
    'an' => 'AN',
    'first_name' => 'ชื่อ',
    'last_name' => 'นามสกุล',
    'origin' => 'ต้นทาง',
    'destination' => 'ปลายทาง',
    'diagnosis' => 'วินิจฉัยเบื้องต้น',
] as $key => $label) {
    if ($data[$key] === '') {
        $errors[] = "กรุณากรอก{$label}";
    }
}
if (!isset($MISSIONS[$data['mission_type']])) {
    $errors[] = 'กรุณาเลือกภารกิจ';
}
if (!isset($AIRCRAFTS[$data['aircraft_type']])) {
    $errors[] = 'กรุณาเลือกชนิดอากาศยาน';
}
if (!isset($REMARKS[$data['remark_type']])) {
    $errors[] = 'กรุณาเลือกหมายเหตุ';
}

$detailUrl = 'index.php?page=air_evac_detail&id=' . $id;
$editUrl = 'index.php?page=air_evac_edit&id=' . $id;
if ($errors) {
    $_SESSION['old'] = $data;
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => implode(' / ', $errors)];
    header('Location: ' . $editUrl);
    exit;
}

try {
    $sql = 'UPDATE air_patients SET
              `date` = :date, hn = :hn, an = :an, rank_code = :rank_code,
              first_name = :first_name, last_name = :last_name, unit = :unit,
              mission_type = :mission_type, mission_name = :mission_name,
              origin = :origin, destination = :destination, diagnosis = :diagnosis,
              aircraft_type = :aircraft_type, remark_type = :remark_type
            WHERE id = :id';
    $data['id'] = $id;
    $pdo->prepare($sql)->execute($data);
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'แก้ไขข้อมูลผู้ป่วยเรียบร้อยแล้ว'];
    header('Location: ' . $editUrl);
} catch (PDOException $e) {
    error_log('Air evacuation patient update failed: ' . $e->getMessage());
    $_SESSION['old'] = array_filter($data, 'is_string');
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'แก้ไขข้อมูลไม่สำเร็จ'];
    header('Location: ' . $detailUrl);
}
exit;
