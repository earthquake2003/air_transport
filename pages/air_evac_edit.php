<?php
$rawId = $_GET['id'] ?? '';
$id = is_string($rawId) && ctype_digit($rawId) ? (int)$rawId : 0;
if ($id < 1) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ไม่พบรายการผู้ป่วยที่ต้องการแก้ไข'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM air_patients WHERE id = :id');
$stmt->execute([':id' => $id]);
$record = $stmt->fetch();
if (!$record) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'ไม่พบรายการผู้ป่วยที่ต้องการแก้ไข'];
    header('Location: index.php?page=air_evac_list');
    exit;
}

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$value = static fn($key) => e($old[$key] ?? $record[$key] ?? '');
$selected = static fn($key, $option) => (($old[$key] ?? $record[$key] ?? '') == $option) ? 'selected' : '';
?>
<div class="admin-page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
  <div>
    <div class="eyebrow">Edit patient record</div>
    <h1>แก้ไขข้อมูลผู้ป่วย</h1>
    <p>HN <?= e($record['hn']) ?> · AN <?= e($record['an'] ?? '-') ?></p>
  </div>
  <a href="index.php?page=air_evac_detail&id=<?= (int)$record['id'] ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>กลับหน้าประวัติ
  </a>
</div>

<div class="evac-page">
  <div class="card evac-card">
    <div class="evac-card-header">
      <h2 class="h5 mb-0"><i class="bi bi-pencil-square me-2" aria-hidden="true"></i>แก้ไขรายละเอียดการลำเลียง</h2>
    </div>
    <div class="card-body">
      <form method="post" action="index.php?page=air_evac_update" class="row g-2">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">

        <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-person-vcard" aria-hidden="true"></i>ข้อมูลผู้ป่วย</h3></div>
        <div class="col-md-4">
          <label class="form-label" for="patientDate">วันที่ <span class="text-danger">*</span></label>
          <input type="date" id="patientDate" name="date" class="form-control" required value="<?= $value('date') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="patientHn">HN <span class="text-danger">*</span></label>
          <input type="text" id="patientHn" name="hn" class="form-control" maxlength="20" required value="<?= $value('hn') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="patientAn">AN <span class="text-danger">*</span></label>
          <input type="text" id="patientAn" name="an" class="form-control" maxlength="20" required value="<?= $value('an') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label" for="patientRank">ยศ</label>
          <input type="text" id="patientRank" name="rank_code" class="form-control" maxlength="20" value="<?= $value('rank_code') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label" for="patientFirstName">ชื่อ <span class="text-danger">*</span></label>
          <input type="text" id="patientFirstName" name="first_name" class="form-control" maxlength="50" required value="<?= $value('first_name') ?>">
        </div>
        <div class="col-md-5">
          <label class="form-label" for="patientLastName">นามสกุล <span class="text-danger">*</span></label>
          <input type="text" id="patientLastName" name="last_name" class="form-control" maxlength="50" required value="<?= $value('last_name') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="patientUnit">หน่วย</label>
          <input type="text" id="patientUnit" name="unit" class="form-control" maxlength="100" value="<?= $value('unit') ?>">
        </div>

        <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>ข้อมูลภารกิจ</h3></div>
        <div class="col-md-6">
          <label class="form-label" for="missionType">ภารกิจ <span class="text-danger">*</span></label>
          <select id="missionType" name="mission_type" class="form-select" required>
            <option value="">-- เลือก --</option>
            <?php foreach ($MISSIONS as $key => $name): ?>
              <option value="<?= (int)$key ?>" <?= $selected('mission_type', $key) ?>><?= (int)$key ?>. <?= e($name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="missionName">ชื่อภารกิจ</label>
          <input type="text" id="missionName" name="mission_name" class="form-control" maxlength="100" value="<?= $value('mission_name') ?>">
        </div>

        <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-signpost-split" aria-hidden="true"></i>เส้นทางและการวินิจฉัย</h3></div>
        <div class="col-md-6">
          <label class="form-label" for="patientOrigin">ต้นทาง <span class="text-danger">*</span></label>
          <input type="text" id="patientOrigin" name="origin" class="form-control" maxlength="100" required value="<?= $value('origin') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="patientDestination">ปลายทาง <span class="text-danger">*</span></label>
          <input type="text" id="patientDestination" name="destination" class="form-control" maxlength="100" required value="<?= $value('destination') ?>">
        </div>
        <div class="col-12">
          <label class="form-label" for="patientDiagnosis">วินิจฉัยเบื้องต้น <span class="text-danger">*</span></label>
          <textarea id="patientDiagnosis" name="diagnosis" class="form-control" maxlength="255" rows="2" required><?= $value('diagnosis') ?></textarea>
        </div>

        <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-airplane-engines" aria-hidden="true"></i>อากาศยานและหมายเหตุ</h3></div>
        <div class="col-md-6">
          <label class="form-label" for="aircraftType">ชนิดอากาศยาน <span class="text-danger">*</span></label>
          <select id="aircraftType" name="aircraft_type" class="form-select" required>
            <option value="">-- เลือก --</option>
            <?php foreach ($AIRCRAFTS as $key => $name): ?>
              <option value="<?= (int)$key ?>" <?= $selected('aircraft_type', $key) ?>><?= (int)$key ?>. <?= e($name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label" for="remarkType">หมายเหตุ <span class="text-danger">*</span></label>
          <select id="remarkType" name="remark_type" class="form-select" required>
            <option value="">-- เลือก --</option>
            <?php foreach ($REMARKS as $key => $name): ?>
              <option value="<?= (int)$key ?>" <?= $selected('remark_type', $key) ?>><?= e($name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 d-flex flex-column-reverse flex-sm-row justify-content-sm-between gap-2 evac-actions">
          <a href="index.php?page=air_evac_detail&id=<?= (int)$record['id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg me-1" aria-hidden="true"></i>ยกเลิก
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>บันทึกการแก้ไข
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
