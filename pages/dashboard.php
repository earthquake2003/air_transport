<?php
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$nextMonth = date('Y-m-01', strtotime('first day of next month'));

$totalRecords = (int)$pdo->query('SELECT COUNT(*) FROM air_patients')->fetchColumn();
$monthStmt = $pdo->prepare('SELECT COUNT(*) FROM air_patients WHERE `date` >= :month_start AND `date` < :next_month');
$monthStmt->execute([
    ':month_start' => $monthStart,
    ':next_month' => $nextMonth,
]);
$monthRecords = (int)$monthStmt->fetchColumn();
$todayStmt = $pdo->prepare('SELECT COUNT(*) FROM air_patients WHERE `date` = :today');
$todayStmt->execute([':today' => $today]);
$todayRecords = (int)$todayStmt->fetchColumn();

$aircraftCounts = array_fill_keys(array_keys($AIRCRAFTS), 0);
$aircraftStmt = $pdo->query(
    'SELECT aircraft_type, COUNT(*) AS record_count
     FROM air_patients
     GROUP BY aircraft_type'
);
foreach ($aircraftStmt->fetchAll() as $row) {
    $aircraftType = (int)$row['aircraft_type'];
    if (array_key_exists($aircraftType, $aircraftCounts)) {
        $aircraftCounts[$aircraftType] = (int)$row['record_count'];
    } else {
        $AIRCRAFTS[$aircraftType] = 'อื่น ๆ';
        $aircraftCounts[$aircraftType] = (int)$row['record_count'];
    }
}

$remarkCounts = array_fill_keys(array_keys($REMARKS), 0);
$remarkStmt = $pdo->query(
    'SELECT remark_type, COUNT(*) AS record_count
     FROM air_patients
     GROUP BY remark_type'
);
foreach ($remarkStmt->fetchAll() as $row) {
    $remarkType = (int)$row['remark_type'];
    if (array_key_exists($remarkType, $remarkCounts)) {
        $remarkCounts[$remarkType] = (int)$row['record_count'];
    } else {
        $REMARKS[$remarkType] = 'อื่น ๆ';
        $remarkCounts[$remarkType] = (int)$row['record_count'];
    }
}

$aircraftChart = [
    'labels' => array_values($AIRCRAFTS),
    'values' => array_values($aircraftCounts),
];
$remarkChart = [
    'labels' => array_values($REMARKS),
    'values' => array_values($remarkCounts),
];

$recentStmt = $pdo->query(
    'SELECT id, `date`, hn, an, first_name, last_name, origin, destination
     FROM air_patients
     ORDER BY `date` DESC, id DESC
     LIMIT 8'
);
$recentRecords = $recentStmt->fetchAll();
$formatThaiDate = static function ($value) {
    $timestamp = strtotime($value);
    return date('d/m/', $timestamp) . (date('Y', $timestamp) + 543);
};
?>
<div class="admin-page-heading d-flex flex-wrap justify-content-between align-items-end gap-3">
  <div>
    <div class="eyebrow">Admin overview</div>
    <h1>แดชบอร์ด</h1>
    <p>ภาพรวมข้อมูลผู้ป่วยลำเลียงทางอากาศประจำวันที่ <?= e($formatThaiDate($today)) ?></p>
  </div>
  
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-4">
    <section class="admin-stat-card">
      <div class="admin-stat-icon"><i class="bi bi-people" aria-hidden="true"></i></div>
      <div>
        <div class="admin-stat-label">ข้อมูลทั้งหมด</div>
        <div class="admin-stat-value"><?= number_format($totalRecords) ?></div>
      </div>
    </section>
  </div>
  <div class="col-sm-6 col-xl-4">
    <section class="admin-stat-card">
      <div class="admin-stat-icon stat-indigo"><i class="bi bi-calendar3" aria-hidden="true"></i></div>
      <div>
        <div class="admin-stat-label">รายการเดือนนี้</div>
        <div class="admin-stat-value"><?= number_format($monthRecords) ?></div>
      </div>
    </section>
  </div>
  <div class="col-sm-6 col-xl-4">
    <section class="admin-stat-card">
      <div class="admin-stat-icon stat-green"><i class="bi bi-calendar-check" aria-hidden="true"></i></div>
      <div>
        <div class="admin-stat-label">รายการวันนี้</div>
        <div class="admin-stat-value"><?= number_format($todayRecords) ?></div>
      </div>
    </section>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-xl-7">
    <section class="admin-panel admin-chart-panel">
      <div class="admin-panel-heading">
        <div>
          <h2><i class="bi bi-airplane-engines me-2 text-info" aria-hidden="true"></i>จำนวนการใช้อากาศยาน</h2>
          <p>สรุปจำนวนรายการแยกตามชนิดอากาศยาน</p>
        </div>
        <span class="admin-chart-badge"><i class="bi bi-bar-chart-fill me-1" aria-hidden="true"></i>แท่ง</span>
      </div>
      <div class="admin-chart-body">
        <canvas id="aircraftChart" role="img" aria-label="กราฟแท่งจำนวนรายการแยกตามชนิดอากาศยาน"></canvas>
      </div>
    </section>
  </div>
  <div class="col-xl-5">
    <section class="admin-panel admin-chart-panel">
      <div class="admin-panel-heading">
        <div>
          <h2><i class="bi bi-pie-chart-fill me-2 text-info" aria-hidden="true"></i>สัดส่วนหมายเหตุ</h2>
          <p>สรุปประเภทหมายเหตุที่บันทึก</p>
        </div>
        <span class="admin-chart-badge"><i class="bi bi-pie-chart me-1" aria-hidden="true"></i>โดนัท</span>
      </div>
      <div class="admin-chart-body admin-doughnut-body">
        <canvas id="remarkChart" role="img" aria-label="กราฟโดนัทสัดส่วนประเภทหมายเหตุ"></canvas>
      </div>
    </section>
  </div>
</div>

<section class="admin-panel admin-recent-panel">
  <div class="admin-panel-heading">
    <div>
      <h2><i class="bi bi-clock-history me-2 text-info" aria-hidden="true"></i>รายการล่าสุด</h2>
      <p>ข้อมูลการลำเลียงที่บันทึกล่าสุดในระบบ</p>
    </div>
    <a href="index.php?page=air_evac_list" class="btn btn-outline-primary btn-sm">
      ดูรายการทั้งหมด<i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
    </a>
  </div>
  <div class="table-responsive">
    <table class="table admin-recent-table">
      <thead>
        <tr>
          <th>วันที่</th><th>HN / AN</th><th>ชื่อผู้ป่วย</th><th>เส้นทาง</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$recentRecords): ?>
        <tr><td colspan="4" class="admin-empty"><i class="bi bi-inbox" aria-hidden="true"></i>ยังไม่มีรายการในระบบ</td></tr>
      <?php else: ?>
        <?php foreach ($recentRecords as $record): ?>
          <tr>
            <td class="text-nowrap"><?= e($formatThaiDate($record['date'])) ?></td>
            <td><span class="admin-record-id"><?= e($record['hn']) ?></span><br><small class="text-muted">AN <?= e($record['an'] ?? '-') ?></small></td>
            <td><?= e(trim($record['first_name'] . ' ' . $record['last_name'])) ?></td>
            <td><?= e($record['origin']) ?><i class="bi bi-arrow-right mx-2 text-info" aria-hidden="true"></i><?= e($record['destination']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script>
  const aircraftData = <?= json_encode($aircraftChart, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const remarkData = <?= json_encode($remarkChart, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const chartPalette = ['#0e7490', '#4f46e5', '#16a36a', '#f59e0b', '#e05b75', '#64748b'];

  if (window.Chart) {
    Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", sans-serif';
    Chart.defaults.color = '#71818b';

    new Chart(document.getElementById('aircraftChart'), {
      type: 'bar',
      data: {
        labels: aircraftData.labels,
        datasets: [{
          data: aircraftData.values,
          backgroundColor: aircraftData.labels.map((_, index) => chartPalette[index % chartPalette.length]),
          borderRadius: 7,
          borderSkipped: false,
          maxBarThickness: 34
        }]
      },
      options: {
        indexAxis: 'y',
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            displayColors: false,
            callbacks: { label: (context) => ` ${context.parsed.x.toLocaleString('th-TH')} รายการ` }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: { precision: 0 },
            grid: { color: '#edf2f5' },
            border: { display: false }
          },
          y: {
            grid: { display: false },
            border: { display: false }
          }
        }
      }
    });

    new Chart(document.getElementById('remarkChart'), {
      type: 'doughnut',
      data: {
        labels: remarkData.labels,
        datasets: [{
          data: remarkData.values,
          backgroundColor: remarkData.labels.map((_, index) => chartPalette[index % chartPalette.length]),
          borderColor: '#fff',
          borderWidth: 4,
          hoverOffset: 7
        }]
      },
      options: {
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { usePointStyle: true, pointStyle: 'circle', padding: 18, boxWidth: 8 }
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                const percentage = total ? (context.parsed / total * 100).toFixed(1) : '0.0';
                return ` ${context.label}: ${context.parsed.toLocaleString('th-TH')} รายการ (${percentage}%)`;
              }
            }
          }
        }
      }
    });
  }
</script>