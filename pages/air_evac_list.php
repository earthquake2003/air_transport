<?php
// ---------- รับค่าค้นหา (GET) แล้วกรองที่ฝั่ง SQL ----------
$hn   = trim($_GET['hn'] ?? '');
$date = trim($_GET['date'] ?? '');
if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = '';

$where  = [];
$params = [];
if ($hn !== '') {
    $where[] = '`hn` LIKE :hn';
    $params[':hn'] = '%' . $hn . '%';
}
if ($date !== '') {
    $where[] = '`date` = :date';
    $params[':date'] = $date;
}

$sql = 'SELECT * FROM air_patients'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY `date` DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="card air-list-card">

  <div class="card-header air-list-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <div class="small text-uppercase fw-bold opacity-75 mb-1"></div>
      <h1 class="h5 mb-0"><i class="fa-solid fa-helicopter me-2" aria-hidden="true"></i>รายการผู้ป่วยลำเลียงทางอากาศ</h1>
      <?php if (admin_logged_in()): ?>
        <div class="small mt-1 opacity-75"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>คลิกแถวเพื่อดูรายละเอียดหรือแก้ไขข้อมูล</div>
      <?php endif; ?>
    </div>
    
  </div>

  <form method="get" action="index.php" class="border-bottom filter-bar">
    <input type="hidden" name="page" value="air_evac_list">
    <div class="row g-3 align-items-end">
      <div class="col-lg-6">
        <label for="fHn" class="form-label"><i class="fa-solid fa-magnifying-glass me-1"></i>ค้นหาผู้ป่วย</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
          <input type="text" id="fHn" name="hn" class="form-control"
                 placeholder="ค้นหาด้วย HN..." value="<?= e($hn) ?>">
        </div>
      </div>

      <div class="col-lg-4 col-md-8">
        <label for="fDate" class="form-label"><i class="fa-regular fa-calendar me-1"></i>กรองตามวันที่</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-regular fa-calendar-check"></i></span>
          <input type="date" id="fDate" name="date" class="form-control" value="<?= e($date) ?>">
        </div>
      </div>

      <div class="col-lg-2 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-search flex-grow-1">
          <i class="fa-solid fa-magnifying-glass me-1"></i>ค้นหา
        </button>
        <a href="index.php?page=air_evac_list" class="btn btn-outline-secondary" title="ล้างตัวกรอง">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>
    </div>
    <?php if ($hn !== '' || $date !== ''): ?>
      <div class="small text-muted mt-2">พบ <?= count($rows) ?> รายการ</div>
    <?php endif; ?>
  </form>

  <div class="card-body air-table-wrap">
    <div class="table-responsive">
      <table id="airEvacTable" class="table mb-0 align-middle w-100">
        <thead>
          <tr>
            <th>วันที่</th><th>HN</th><th>AN</th><th>ยศ</th><th>ชื่อ-สกุล</th><th>หน่วย</th>
            <th>ภารกิจ</th><th>ต้นทาง → ปลายทาง</th><th>วินิจฉัย</th>
            <th>อากาศยาน</th><th>หมายเหตุ</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
            $ts   = strtotime($r['date']);
            $thai = date('d/m/', $ts) . (date('Y', $ts) + 543);
        ?>
          <tr class="<?= admin_logged_in() ? 'air-table-clickable' : '' ?>"
              <?php if (admin_logged_in()): ?>
              data-detail-url="index.php?page=air_evac_detail&id=<?= (int)$r['id'] ?>"
              tabindex="0" role="link"
              aria-label="<?= e('ดูรายละเอียดผู้ป่วย HN ' . $r['hn']) ?>"
              <?php endif; ?>>
            <td data-order="<?= e($r['date']) ?>"><?= e($thai) ?></td>
            <td class="patient-hn"><?= e($r['hn']) ?></td>
            <td class=""><?= e($r['an'] ?? '-') ?></td>
            <td><?= e($r['rank_code'] ?? '-') ?></td>
            <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
            <td><?= e($r['unit'] ?? '-') ?></td>
            <td><?= e($MISSIONS[$r['mission_type']] ?? '-') ?>
                <?php if ($r['mission_name']): ?><br><small class="text-muted"><?= e($r['mission_name']) ?></small><?php endif; ?></td>
            <td class="route-cell"><?= e($r['origin']) ?><span class="route-arrow">&rarr;</span><?= e($r['destination']) ?></td>
            <td class="diagnosis-cell"><?= e($r['diagnosis']) ?></td>
            <td><?= e($AIRCRAFTS[$r['aircraft_type']] ?? '-') ?></td>
            <td><?= e($REMARKS[$r['remark_type']] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script>
// DataTables ใช้เฉพาะเรียงลำดับ + แบ่งหน้า (การค้นหาทำที่ฝั่ง PHP แล้ว)
if (window.DataTable) {
  new DataTable('#airEvacTable', {
    order: [[0, 'desc']],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
    layout: { topStart: 'pageLength', topEnd: null },
    language: { url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/th.json' }
  });
}

document.querySelector('#airEvacTable tbody')?.addEventListener('click', function (event) {
  const row = event.target.closest('tr[data-detail-url]');
  if (!row || event.target.closest('a, button, input, select, textarea')) return;
  window.location.href = row.dataset.detailUrl;
});

document.querySelector('#airEvacTable tbody')?.addEventListener('keydown', function (event) {
  const row = event.target.closest('tr[data-detail-url]');
  if (!row || (event.key !== 'Enter' && event.key !== ' ')) return;
  event.preventDefault();
  window.location.href = row.dataset.detailUrl;
});
</script>