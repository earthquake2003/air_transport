<?php
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$nextMonth = date('Y-m-01', strtotime('first day of next month'));
$currentYear = (int)date('Y');
$selectedYear = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$selectedYear = $selectedYear !== false && $selectedYear !== null
    && $selectedYear >= 2000 && $selectedYear <= $currentYear + 1
    ? $selectedYear
    : $currentYear;
$requestedMonth = $_GET['month'] ?? (int)date('n');
$selectedMonth = $requestedMonth === 'year' ? null : filter_var($requestedMonth, FILTER_VALIDATE_INT);
if ($requestedMonth !== 'year' && ($selectedMonth === false || $selectedMonth === null || $selectedMonth < 1 || $selectedMonth > 12)) {
    $selectedMonth = (int)date('n');
}
$trendStart = $selectedMonth === null
    ? sprintf('%04d-01-01', $selectedYear)
    : sprintf('%04d-%02d-01', $selectedYear, $selectedMonth);
$trendEndExclusive = (new DateTimeImmutable($trendStart))
    ->modify($selectedMonth === null ? '+1 year' : '+1 month')
    ->format('Y-m-d');
$minYear = (int)$pdo->query('SELECT MIN(YEAR(`date`)) FROM air_patients')->fetchColumn();
$firstYear = $minYear > 0 ? min($minYear, $currentYear) : $currentYear;

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
    "SELECT DATE_FORMAT(`date`, '" . ($selectedMonth === null ? '%Y-%m' : '%Y-%m-%d') . "') AS period_key,
            DATE_FORMAT(`date`, '%Y-%m') AS month_key, aircraft_type, remark_type, COUNT(*) AS record_count
     FROM air_patients
     WHERE `date` >= :trend_start AND `date` < :trend_end
     GROUP BY period_key, month_key, aircraft_type, remark_type"
);
$trendStmt->execute([
    ':trend_start' => $trendStart,
    ':trend_end' => $trendEndExclusive,
]);
$trendCounts = [];
$aircraftMonthlyCounts = [];
$remarkMonthlyCounts = [];
$trendPeriodCounts = [];
foreach ($trendStmt->fetchAll() as $row) {
    $monthKey = $row['month_key'];
    $periodKey = $row['period_key'];
    $recordCount = (int)$row['record_count'];
    $aircraftType = (int)$row['aircraft_type'];
    $remarkType = (int)$row['remark_type'];

    $trendCounts[$monthKey] = ($trendCounts[$monthKey] ?? 0) + $recordCount;
    $trendPeriodCounts[$periodKey] = ($trendPeriodCounts[$periodKey] ?? 0) + $recordCount;
    $AIRCRAFTS[$aircraftType] = $AIRCRAFTS[$aircraftType] ?? 'อื่น ๆ';
    $REMARKS[$remarkType] = $REMARKS[$remarkType] ?? 'อื่น ๆ';
    $aircraftMonthlyCounts[$monthKey][$aircraftType] = ($aircraftMonthlyCounts[$monthKey][$aircraftType] ?? 0) + $recordCount;
    $remarkMonthlyCounts[$monthKey][$remarkType] = ($remarkMonthlyCounts[$monthKey][$remarkType] ?? 0) + $recordCount;
}

$thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$trendLabels = [];
$trendValues = [];
$trendMonthKeys = [];
if ($selectedMonth === null) {
    $trendMonth = new DateTimeImmutable($trendStart);
    for ($monthIndex = 0; $monthIndex < 12; $monthIndex++) {
        $monthKey = $trendMonth->format('Y-m');
        $trendMonthKeys[] = $monthKey;
        $trendLabels[] = $thaiMonths[(int)$trendMonth->format('n') - 1] . ' ' . ((int)$trendMonth->format('Y') + 543);
        $trendValues[] = $trendCounts[$monthKey] ?? 0;
        $trendMonth = $trendMonth->modify('+1 month');
    }
} else {
    $daysInMonth = (int)(new DateTimeImmutable($trendStart))->format('t');
    $trendMonthKeys[] = substr($trendStart, 0, 7);
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $periodKey = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $day);
        $trendLabels[] = (string)$day;
        $trendValues[] = $trendPeriodCounts[$periodKey] ?? 0;
    }
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
      <label for="chartMonth" class="form-label"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>เดือน</label>
      <select id="chartMonth" name="month" class="form-select">
        <option value="year" <?= $selectedMonth === null ? 'selected' : '' ?>>ทั้งปี</option>
        <?php foreach ($thaiMonths as $index => $monthName): ?>
          <option value="<?= $index + 1 ?>" <?= $selectedMonth === $index + 1 ? 'selected' : '' ?>><?= e(trim($monthName, '.')) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-lg-3 col-md-6">
      <label for="chartYear" class="form-label"><i class="bi bi-calendar3 me-2" aria-hidden="true"></i>ปี</label>
      <select id="chartYear" name="year" class="form-select">
        <?php for ($year = $currentYear; $year >= $firstYear; $year--): ?>
          <option value="<?= $year ?>" <?= $selectedYear === $year ? 'selected' : '' ?>><?= $year + 543 ?></option>
        <?php endfor; ?>
      </select>
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

    const chartValueLabels = {
      id: 'chartValueLabels',
      afterDatasetsDraw(chart) {
        const { ctx } = chart;
        ctx.save();
        ctx.font = '600 12px system-ui, -apple-system, "Segoe UI", sans-serif';
        ctx.textBaseline = 'middle';

        if (chart.config.type === 'bar') {
          const meta = chart.getDatasetMeta(0);
          meta.data.forEach((bar, index) => {
            const value = Number(chart.data.datasets[0].data[index]) || 0;
            const label = value.toLocaleString('th-TH');
            const inside = value > 0 && bar.x - bar.base > ctx.measureText(label).width + 16;
            ctx.textAlign = inside ? 'right' : 'left';
            ctx.fillStyle = inside ? '#fff' : '#405565';
            ctx.fillText(label, inside ? bar.x - 8 : bar.x + 8, bar.y);
          });
        } else if (chart.config.type === 'doughnut') {
          const meta = chart.getDatasetMeta(0);
          meta.data.forEach((arc, index) => {
            const value = Number(chart.data.datasets[0].data[index]) || 0;
            if (value === 0 || arc.circumference < .34) return;

            const angle = arc.startAngle + arc.circumference / 2;
            const radius = (arc.innerRadius + arc.outerRadius) / 2;
            const x = arc.x + Math.cos(angle) * radius;
            const y = arc.y + Math.sin(angle) * radius;
            ctx.textAlign = 'center';
            ctx.fillStyle = chart.data.datasets[0].backgroundColor[index] === '#f59e0b' ? '#263746' : '#fff';
            ctx.fillText(value.toLocaleString('th-TH'), x, y);
          });
        }

        ctx.restore();
      }
    };

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
      plugins: [chartValueLabels],
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
            grace: '14%',
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
      plugins: [chartValueLabels],
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
            labels: {
              usePointStyle: true,
              pointStyle: 'circle',
              padding: 18,
              boxWidth: 8,
              generateLabels(chart) {
                const labels = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                return labels.map((item) => {
                  const value = chart.data.datasets[0].data[item.index] ?? 0;
                  return { ...item, text: `${item.text} (${Number(value).toLocaleString('th-TH')})` };
                });
              }
            }
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