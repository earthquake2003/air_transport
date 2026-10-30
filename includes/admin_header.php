<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>หลังบ้าน | ระบบข้อมูลผู้ป่วยลำเลียงทางอากาศ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/2.3.2/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link href="assets/css/app.css?v=<?= filemtime(__DIR__ . '/../assets/css/app.css') ?>" rel="stylesheet">
  <link href="assets/css/admin.css?v=<?= filemtime(__DIR__ . '/../assets/css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-app">
<nav class="navbar navbar-expand-lg navbar-dark admin-topbar">
  <div class="container-fluid px-3 px-lg-4">
    <a class="navbar-brand admin-brand" href="index.php?page=admin_dashboard">
      <span class="admin-brand-icon"><i class="bi bi-airplane-engines" aria-hidden="true"></i></span>
      <span>ระบบลำเลียงผู้ป่วยทางอากาศ<small>ADMINISTRATION</small></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminTopNav"
            aria-controls="adminTopNav" aria-expanded="false" aria-label="เปิดหรือปิดเมนู">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminTopNav">
      <div class="ms-auto d-flex flex-column flex-lg-row align-items-lg-center gap-3 py-3 py-lg-0">
        <div class="admin-user">
          <span class="admin-user-avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
          <span><small>ผู้ดูแลระบบ</small><strong><?= e($_SESSION['admin_name'] ?? '') ?></strong></span>
        </div>
        <!--<a class="btn btn-admin-light btn-sm" href="index.php?page=admin_form">
          <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>บันทึกข้อมูลใหม่
        </a>-->
        <form id="adminLogoutForm" method="post" action="index.php?page=logout" class="m-0">
          <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
          <button type="button" id="adminLogoutButton" class="btn btn-admin-logout btn-sm w-100">
            <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>ออกจากระบบ
          </button>
        </form>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid admin-layout">
  <div class="admin-layout-row" id="adminLayout">
    <aside class="admin-sidebar-column" id="adminSidebar">
      <div class="admin-sidebar-inner">
        <div class="admin-sidebar-label" id="adminSidebarToggle" role="button" tabindex="0"
             aria-controls="adminSidebar" aria-expanded="true" aria-label="พับหรือขยายเมนูด้านข้าง"
             title="คลิกเพื่อพับหรือขยายเมนู" onclick="toggleAdminSidebar()"
             onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); toggleAdminSidebar(); }">
          <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
          <span>เมนูหลัก</span>
          <i class="bi bi-chevron-left admin-sidebar-chevron" aria-hidden="true"></i>
        </div>
        <nav class="nav nav-pills flex-column admin-nav">
          <a href="index.php?page=admin_dashboard"
             class="nav-link <?= $page === 'admin_dashboard' ? 'active' : '' ?>"
             <?= $page === 'admin_dashboard' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span>แดชบอร์ด</span>
          </a>
          <a href="index.php?page=admin_form"
             class="nav-link <?= $page === 'admin_form' ? 'active' : '' ?>"
             <?= $page === 'admin_form' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-clipboard2-plus" aria-hidden="true"></i><span>บันทึกข้อมูล</span>
          </a>
          <a href="index.php?page=air_evac_list"
             class="nav-link <?= $page === 'air_evac_list' ? 'active' : '' ?>"
             <?= $page === 'air_evac_list' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-clipboard2-pulse" aria-hidden="true"></i><span>รายการลำเลียงผู้ป่วย</span>
          </a>
        </nav>
      </div>
    </aside>
    <main class="admin-main admin-main-column">