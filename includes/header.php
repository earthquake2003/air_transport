<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ระบบข้อมูลผู้ป่วยลำเลียงทางอากาศ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="assets/css/app.css?v=<?= filemtime(__DIR__ . '/../assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark site-navbar">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand" href="index.php">
      <span class="brand-icon"><i class="bi bi-airplane-engines" aria-hidden="true"></i></span>
      <span>ระบบลำเลียงผู้ป่วยทางอากาศ<span class="brand-caption">AIR EVACUATION MANAGEMENT</span></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation"
            aria-controls="mainNavigation" aria-expanded="false" aria-label="เปิดหรือปิดเมนู">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavigation">
      <div class="navbar-nav ms-auto align-items-lg-center">
        <a class="nav-link site-admin-link ms-lg-2" href="index.php?page=login">
          <i class="bi bi-person-lock me-1" aria-hidden="true"></i>เข้าสู่ระบบ Admin
        </a>
      </div>
    </div>
  </div>
</nav>
<main class="container py-4">
