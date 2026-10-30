<?php
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$nextMonth = date('Y-m-01', strtotime('first day of next month'));
$defaultTrendStart = date('Y-m-01', strtotime('-11 months', strtotime($monthStart)));
$trendStart = $defaultTrendStart;
$trendEndDate = $today;
$chartDateError = '';

$requestedStart = $_GET['start_date'] ?? null;
$requestedEnd = $_GET['end_date'] ?? null;
$validDate = static function ($value) {
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }

    [$year, $month, $day] = array_map('intval', explode('-', $value));
    return checkdate($month, $day, $year);
};

if ($requestedStart !== null || $requestedEnd !== null) {
    if (!$validDate($requestedStart) || !$validDate($requestedEnd)) {
        $chartDateError = 'กรุณาระบุวันที่เริ่มต้นและวันที่สิ้นสุดให้ถูกต้อง';
    } elseif ($requestedStart > $requestedEnd) {
        $chartDateError = 'วันที่เริ่มต้นต้องไม่อยู่หลังวันที่สิ้นสุด';
    } else {
        $trendStart = $requestedStart;
        $trendEndDate = $requestedEnd;
    }
}
$trendEndExclusive = (new DateTimeImmutable($trendEndDate))->modify('+1 day')->format('Y-m-d');

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

$trendStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(`date`, '%Y-%m') AS month_key, aircraft_type, remark_type, COUNT(*) AS record_count
     FROM air_patients
     WHERE `date` >= :trend_start AND `date` < :trend_end
     GROUP BY month_key, aircraft_type, remark_type"
);
$trendStmt->execute([
    ':trend_start' => $trendStart,
    ':trend_end' => $trendEndExclusive,
]);
$trendCounts = [];
$aircraftMonthlyCounts = [];
$remarkMonthlyCounts = [];
foreach ($trendStmt->fetchAll() as $row) {
    $monthKey = $row['month_key'];
    $recordCount = (int)$row['record_count'];
    $aircraftType = (int)$row['aircraft_type'];
    $remarkType = (int)$row['remark_type'];

    $trendCounts[$monthKey] = ($trendCounts[$monthKey] ?? 0) + $recordCount;
    $AIRCRAFTS[$aircraftType] = $AIRCRAFTS[$aircraftType] ?? 'อื่น ๆ';
    $REMARKS[$remarkType] = $REMARKS[$remarkType] ?? 'อื่น ๆ';
    $aircraftMonthlyCounts[$monthKey][$aircraftType] = ($aircraftMonthlyCounts[$monthKey][$aircraftType] ?? 0) + $recordCount;
    $remarkMonthlyCounts[$monthKey][$remarkType] = ($remarkMonthlyCounts[$monthKey][$remarkType] ?? 0) + $recordCount;
}

$thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$trendLabels = [];
$trendValues = [];
$trendMonthKeys = [];
$trendMonth = (new DateTimeImmutable($trendStart))->modify('first day of this month');
$trendLastMonth = new DateTimeImmutable($trendEndDate);
$trendLastMonth = $trendLastMonth->modify('first day of this month');
while ($trendMonth <= $trendLastMonth) {
    $monthKey = $trendMonth->format('Y-m');
    $trendMonthKeys[] = $monthKey;
    $trendLabels[] = $thaiMonths[(int)$trendMonth->format('n') - 1] . ' ' . ((int)$trendMonth->format('Y') + 543);
    $trendValues[] = $trendCounts[$monthKey] ?? 0;
    $trendMonth = $trendMonth->modify('+1 month');
}

$aircraftChart = ['labels' => array_values($AIRCRAFTS), 'values' => []];
foreach (array_keys($AIRCRAFTS) as $aircraftType) {
    $monthlyCounts = array_map(
        static fn($monthKey) => $aircraftMonthlyCounts[$monthKey][$aircraftType] ?? 0,
        $trendMonthKeys
    );
    $aircraftChart['values'][] = array_sum($monthlyCounts);
}

$remarkChart = ['labels' => array_values($REMARKS), 'values' => []];
foreach (array_keys($REMARKS) as $remarkType) {
    $monthlyCounts = array_map(
        static fn($monthKey) => $remarkMonthlyCounts[$monthKey][$remarkType] ?? 0,
        $trendMonthKeys
    );
    $remarkChart['values'][] = array_sum($monthlyCounts);
}

$trendChart = [
    'labels' => $trendLabels,
    'values' => $trendValues,
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

<form method="get" action="index.php" class="admin-chart-date-filter mb-4">
  <input type="hidden" name="page" value="admin_dashboard">
  <div class="row g-3 align-items-end">
    <div class="col-lg-3 col-md-6">
      <label for="chartStartDate" class="form-label"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>วันที่เริ่มต้น</label>
      <input id="chartStartDate" type="date" name="start_date" class="form-control"
             value="<?= e($trendStart) ?>" required>
    </div>
    <div class="col-lg-3 col-md-6">
      <label for="chartEndDate" class="form-label"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>วันที่สิ้นสุด</label>
      <input id="chartEndDate" type="date" name="end_date" class="form-control"
             value="<?= e($trendEndDate) ?>" required>
    </div>
    <div class="col-lg-3 col-md-6">
      <button type="submit" class="btn btn-chart-filter w-100">
        <i class="bi bi-funnel-fill me-2" aria-hidden="true"></i>ประมวลผล
      </button>
    </div>
    <div class="col-lg-3 col-md-6">
      <a href="index.php?page=admin_dashboard" class="btn btn-chart-reset w-100">
        <i class="bi bi-arrow-counterclockwise me-2" aria-hidden="true"></i>ล้างค่า
      </a>
    </div>
  </div>
  <?php if ($chartDateError !== ''): ?>
    <div class="alert alert-danger py-2 mt-3 mb-0" role="alert"><?= e($chartDateError) ?></div>
  <?php endif; ?>
</form>

<div class="row g-3 mb-4">
  <div class="col-xl-7">
    <section class="admin-panel admin-chart-panel">
      <div class="admin-panel-heading">
        <div>
          <h2><i class="bi bi-airplane-engines me-2 text-info" aria-hidden="true"></i>จำนวนการใช้อากาศยาน</h2>
          <p>จำนวนการใช้อากาศยานตามช่วงเวลาที่เลือก</p>
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
          <p>สัดส่วนหมายเหตุตามช่วงเวลาที่เลือก</p>
        </div>
        <span class="admin-chart-badge"><i class="bi bi-pie-chart me-1" aria-hidden="true"></i>โดนัท</span>
      </div>
      <div class="admin-chart-body admin-doughnut-body">
        <canvas id="remarkChart" role="img" aria-label="กราฟโดนัทสัดส่วนประเภทหมายเหตุ"></canvas>
      </div>
    </section>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12">
    <section class="admin-panel admin-chart-panel">
      <div class="admin-panel-heading">
        <div>
          <h2><i class="bi bi-graph-up-arrow me-2 text-info" aria-hidden="true"></i>แนวโน้มการลำเลียงรายเดือน</h2>
          <p>จำนวนรายการลำเลียงรายเดือน แสดงเดือนที่ไม่มีรายการเป็นศูนย์</p>
        </div>
      </div>
      <div class="admin-chart-body admin-trend-chart-body">
        <canvas id="trendChart" role="img" aria-label="กราฟแนวโน้มจำนวนผู้ป่วยลำเลียงรายเดือน"></canvas>
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
  const trendData = <?= json_encode($trendChart, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const chartPalette = ['#0e7490', '#4f46e5', '#16a36a', '#f59e0b', '#e05b75', '#64748b'];

  if (window.Chart) {
    Chart.defaults.font.family = 'system-ui, -apple-system, "Segoe UI", sans-serif';
    Chart.defaults.color = '#71818b';

    const trendChart = new Chart(document.getElementById('trendChart'), {
      type: 'line',
      data: {
        labels: trendData.labels,
        datasets: [{
          label: 'จำนวนรายการ',
          data: trendData.values,
          borderColor: '#0e7490',
          backgroundColor: 'rgba(14, 116, 144, .12)',
          pointBackgroundColor: '#fff',
          pointBorderColor: '#0e7490',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          borderWidth: 3,
          fill: true,
          tension: .35
        }]
      },
      options: {
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
          legend: { display: false },
          tooltip: {
            displayColors: false,
            callbacks: { label: (context) => ` ${context.parsed.y.toLocaleString('th-TH')} รายการ` }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false }
          },
          y: {
            beginAtZero: true,
            ticks: { precision: 0 },
            grid: { color: '#edf2f5' },
            border: { display: false }
          }
        }
      }
    });

    const aircraftChart = new Chart(document.getElementById('aircraftChart'), {
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

    const remarkChart = new Chart(document.getElementById('remarkChart'), {
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