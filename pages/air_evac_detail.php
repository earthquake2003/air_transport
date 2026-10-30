<?php
$rawId = $_GET['id'] ?? '';
$id = is_string($rawId) && ctype_digit($rawId) ? (int)$rawId : 0;
if ($id < 1) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ไม่พบรายการผู้ป่วยที่ต้องการ'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM air_patients WHERE id = :id');
$stmt->execute([':id' => $id]);
$record = $stmt->fetch();
if (!$record) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ไม่พบรายการผู้ป่วยที่ต้องการ'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$display = static function ($value) {
    $value = trim((string)$value);
    return $value !== '' && $value !== '-'
        ? e($value)
        : '<span class="patient-detail-empty">ไม่ระบุ</span>';
};
$timestamp = strtotime($record['date']);
$thaiDate = date('d/m/', $timestamp) . (date('Y', $timestamp) + 543);
$printedAt = date('d/m/Y H:i');
$patientName = trim(($record['rank_code'] ?? '') . ' ' . $record['first_name'] . ' ' . $record['last_name']);
?>
<div class="patient-print-heading">
  <div class="patient-print-name">เวชระเบียนประวัติ: <?= $display($patientName) ?></div>
  <h1>แบบบันทึกเวชระเบียนผู้ป่วย (Medical Record)</h1>
  <p>โรงพยาบาลอานันทมหิดล | พิมพ์เมื่อ: <?= e($printedAt) ?></p>
</div>
<section class="patient-summary-card mt-4">
  <header class="patient-summary-header">
    <span class="patient-summary-avatar"><i class="bi bi-person-vcard-fill" aria-hidden="true"></i></span>
    <div class="patient-summary-title">
      <span class="patient-summary-kicker">ข้อมูลผู้ป่วย (PATIENT INFORMATION)</span>
      <div class="patient-summary-name-row">
        <h1><?= $display($patientName) ?></h1>
        <div class="patient-summary-actions">
          <button type="button" class="btn patient-summary-print" onclick="window.print()">
            <i class="bi bi-printer-fill" aria-hidden="true"></i>พิมพ์
          </button>
          <a href="index.php?page=air_evac_edit&id=<?= (int)$record['id'] ?>" class="btn patient-summary-edit">
            <i class="bi bi-pencil-fill" aria-hidden="true"></i>แก้ไขข้อมูล
          </a>
        </div>
      </div>
      <a class="patient-summary-back" href="index.php?page=air_evac_list">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>กลับไปหน้ารายการ
      </a>
    </div>
    <div class="patient-summary-hn">
      <span>HN</span>
      <strong><?= $display($record['hn']) ?></strong>
      <small>AN: <?= $display($record['an'] ?? '') ?></small>
    </div>
  </header>

  <div class="patient-summary-fields">
    <div class="patient-summary-field">
      <span><i class="bi bi-calendar2-check" aria-hidden="true"></i>วันที่ลำเลียง</span>
      <strong><?= e($thaiDate) ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>ภารกิจ</span>
      <strong><?= $display($MISSIONS[$record['mission_type']] ?? '') ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-card-text" aria-hidden="true"></i>ชื่อภารกิจ</span>
      <strong><?= $display($record['mission_name'] ?? '') ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-building" aria-hidden="true"></i>หน่วย</span>
      <strong><?= $display($record['unit'] ?? '') ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-geo-alt" aria-hidden="true"></i>ต้นทาง</span>
      <strong><?= $display($record['origin']) ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-geo-alt-fill" aria-hidden="true"></i>ปลายทาง</span>
      <strong><?= $display($record['destination']) ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-clipboard2-plus" aria-hidden="true"></i>วินิจฉัยเบื้องต้น</span>
      <strong><?= $display($record['diagnosis']) ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-airplane-engines" aria-hidden="true"></i>ชนิดอากาศยาน</span>
      <strong><?= $display($AIRCRAFTS[$record['aircraft_type']] ?? '') ?></strong>
    </div>
    <div class="patient-summary-field">
      <span><i class="bi bi-info-circle" aria-hidden="true"></i>หมายเหตุ</span>
      <strong><?= $display($REMARKS[$record['remark_type']] ?? '') ?></strong>
    </div>
  </div>
</section>
