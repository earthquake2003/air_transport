</main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const adminLayout = document.getElementById('adminLayout');
  const sidebarToggle = document.getElementById('adminSidebarToggle');
  if (localStorage.getItem('adminSidebarCollapsed') === 'true') {
    adminLayout.classList.add('sidebar-collapsed');
    sidebarToggle.setAttribute('aria-expanded', 'false');
  }

  function toggleAdminSidebar() {
    const collapsed = adminLayout.classList.toggle('sidebar-collapsed');
    sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
    localStorage.setItem('adminSidebarCollapsed', String(collapsed));
  }

  document.getElementById('adminLogoutButton')?.addEventListener('click', function () {
    Swal.fire({
      icon: 'warning',
      title: 'ยืนยันการออกจากระบบ',
      text: 'คุณต้องการออกจากระบบใช่หรือไม่?',
      showCancelButton: true,
      confirmButtonText: 'ออกจากระบบ',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      reverseButtons: true,
      focusCancel: true
    }).then((result) => {
      if (result.isConfirmed) {
        document.getElementById('adminLogoutForm').submit();
      }
    });
  });
</script>
<?php if (!empty($_SESSION['flash'])): ?>
<script>
Swal.fire({
  icon: <?= json_encode($_SESSION['flash']['type']) ?>,
  title: <?= json_encode($_SESSION['flash']['msg'], JSON_UNESCAPED_UNICODE) ?>,
  confirmButtonText: 'ตกลง'
});
</script>
<?php unset($_SESSION['flash']); endif; ?>
</body>
</html>