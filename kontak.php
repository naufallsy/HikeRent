<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Kontak'; $nav = 'kontak';
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h2 class="fw-bold mb-1">Hubungi Kami</h2><p class="mb-0 opacity-75">Ada pertanyaan? Kami siap membantu</p></div></section>
<div class="container py-5"><div class="row g-4">
  <?php foreach ([['bi-geo-alt-fill', 'Alamat', 'Jl. Raya Dieng No. 10, Wonosobo, Jawa Tengah'], ['bi-telephone-fill', 'Telepon / WhatsApp', '0812-3456-7890'],
    ['bi-envelope-fill', 'Email', 'halo@hikerent.com'], ['bi-clock-fill', 'Jam Operasional', 'Setiap hari, 08.00 - 20.00 WIB']] as $c): ?>
    <div class="col-md-6 col-lg-3"><div class="card card-soft feature h-100"><div><span class="ico"><i class="bi <?= $c[0] ?>"></i></span></div><h6 class="fw-semibold"><?= $c[1] ?></h6><p class="small text-muted mb-0"><?= $c[2] ?></p></div></div>
  <?php endforeach; ?>
</div>
<div class="text-center mt-5">
  <a class="btn btn-orange btn-lg me-2" target="_blank" rel="noopener" href="https://wa.me/6285284652852?text=<?= rawurlencode('Halo HikeRent, saya ingin bertanya tentang penyewaan alat hiking.') ?>"><i class="bi bi-whatsapp"></i> Chat WhatsApp</a>
</div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
