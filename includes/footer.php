</main>
<footer class="site-footer">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div class="site-footer-brand">
      <span class="site-footer-icon"><i class="bi bi-hospital" aria-hidden="true"></i></span>
      <div>
        <strong>โรงพยาบาลอานันทมหิดล</strong>
        <span></span>
      </div>
    </div>
    <div class="site-footer-copy">
      &copy; <?= date('Y') ?> สำนักงานวิชาการ โรงพยาบาลอานันทมหิดล
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
