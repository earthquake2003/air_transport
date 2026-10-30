<?php
// ค่าเดิมที่กรอกไว้ (กรณีตรวจแล้วไม่ผ่านจะได้ไม่ต้องกรอกใหม่)
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$v = fn($k, $d = '') => e($old[$k] ?? $d);
$sel = fn($k, $val) => (($old[$k] ?? '') == $val) ? 'selected' : '';
?>

<div class="evac-page py-2 py-md-3">
  <div class="card evac-card">
    <div class="evac-card-header">
      <h2 class="h5 mb-0"><i class="bi bi-airplane me-2" aria-hidden="true"></i>รายละเอียดการลำเลียง</h2>
    </div>
    <div class="card-body">
    <form method="post" action="index.php?page=air_evac_save" class="row g-2">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

      <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-person-vcard" aria-hidden="true"></i>ข้อมูลผู้ป่วย</h3></div>

      <!-- 1. วันที่ -->
      <div class="col-md-4">
        <label class="form-label">วันที่ <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control" required
               value="<?= $v('date', date('Y-m-d')) ?>">
      </div>

      <!-- 2. HN -->
      <div class="col-md-4">
        <label for="hn" class="form-label">HN <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="text" id="hn" name="hn" class="form-control" maxlength="20" required
                 value="<?= $v('hn') ?>">
          <button type="button" class="btn btn-air hn-fetch-btn">ดึงข้อมูล</button>
        </div>
      </div>

      <!-- 2. AN -->
      <div class="col-md-4">
        <label class="form-label">AN <span class="text-danger">*</span></label>
        <input type="text" name="an" class="form-control" maxlength="20" required
               value="<?= $v('an') ?>">
      </div>

      <!-- 3. ยศ -->
      <div class="col-md-3">
        <label class="form-label">ยศ</label>
        <input type="text" name="rank_code" class="form-control" maxlength="20"
               value="<?= $v('rank_code') ?>">
      </div>

      <!-- 4. ชื่อ-สกุล -->
      <div class="col-md-4">
        <label class="form-label">ชื่อ</label>
        <input type="text" name="first_name" class="form-control" maxlength="50"
               value="<?= $v('first_name') ?>">
      </div>
      <div class="col-md-5">
        <label class="form-label">นามสกุล</label>
        <input type="text" name="last_name" class="form-control" maxlength="50"
               value="<?= $v('last_name') ?>">
      </div>

      <!-- 5. หน่วย -->
      <div class="col-md-6">
        <label class="form-label">หน่วย</label>
        <input type="text" name="unit" class="form-control" maxlength="100" value="<?= $v('unit') ?>">
      </div>

      <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i>ข้อมูลภารกิจ</h3></div>

      <!-- 6. ภารกิจ -->
      <div class="col-md-6">
        <label class="form-label">ภารกิจ <span class="text-danger">*</span></label>
        <select name="mission_type" class="form-select" required>
          <option value="">-- เลือก --</option>
        <?php foreach ($MISSIONS as $k => $name): ?>
          <option value="<?= $k ?>" <?= $sel('mission_type', $k) ?>><?= $k ?>. <?= e($name) ?></option>
        <?php endforeach; ?>
        </select>
      </div>

      <!-- 7. ชื่อภารกิจ -->
      <div class="col-md-6">
        <label class="form-label">ชื่อภารกิจ</label>
        <input type="text" name="mission_name" class="form-control" maxlength="100"
               value="<?= $v('mission_name') ?>">
      </div>

      <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-signpost-split" aria-hidden="true"></i>เส้นทางและการวินิจฉัย</h3></div>

      <!-- 8-9. ต้นทาง ปลายทาง -->
      <div class="col-md-6">
        <label class="form-label">ต้นทาง <span class="text-danger">*</span></label>
        <input type="text" name="origin" class="form-control" maxlength="100" required
               value="<?= $v('origin') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">ปลายทาง <span class="text-danger">*</span></label>
        <input type="text" name="destination" class="form-control" maxlength="100" required
               value="<?= $v('destination') ?>">
      </div>

      <!-- 10. วินิจฉัยเบื้องต้น -->
      <div class="col-12">
        <label class="form-label">วินิจฉัยเบื้องต้น <span class="text-danger">*</span></label>
        <textarea name="diagnosis" class="form-control" maxlength="255" rows="2" required><?= $v('diagnosis') ?></textarea>
      </div>

      <div class="col-12"><h3 class="evac-section-title"><i class="bi bi-airplane-engines" aria-hidden="true"></i>อากาศยานและหมายเหตุ</h3></div>

      <!-- 11. ชนิดอากาศยาน -->
      <div class="col-md-6">
        <label class="form-label">ชนิดอากาศยาน <span class="text-danger">*</span></label>
        <select name="aircraft_type" class="form-select" required>
          <option value="">-- เลือก --</option>
        <?php foreach ($AIRCRAFTS as $k => $name): ?>
          <option value="<?= $k ?>" <?= $sel('aircraft_type', $k) ?>><?= $k ?>. <?= e($name) ?></option>
        <?php endforeach; ?>
        </select>
      </div>

      <!-- 12. หมายเหตุ -->
      <div class="col-md-6">
        <label class="form-label">หมายเหตุ <span class="text-danger">*</span></label>
        <select name="remark_type" class="form-select" required>
          <option value="">-- เลือก --</option>
        <?php foreach ($REMARKS as $k => $name): ?>
          <option value="<?= $k ?>" <?= $sel('remark_type', $k) ?>><?= e($name) ?></option>
        <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 d-flex flex-column-reverse flex-sm-row justify-content-sm-end gap-2 evac-actions">
        <button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>ล้างฟอร์ม</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1" aria-hidden="true"></i>บันทึกข้อมูล</button>
      </div>
    </form>
    </div>
  </div>
</div>
