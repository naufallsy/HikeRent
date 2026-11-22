<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Beranda'; $nav = 'home';
$unggulan = db_all("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.status='tersedia' ORDER BY a.id_alat ASC LIMIT 8");
$kats = db_all("SELECT k.*, COUNT(a.id_alat) AS jml FROM kategori k LEFT JOIN alat a ON a.id_kategori=k.id_kategori GROUP BY k.id_kategori ORDER BY k.nama_kategori");
include __DIR__ . '/includes/header.php';
?>
<section class="hero"><div class="container">
  <span class="tag"><i class="bi bi-compass"></i> Penyewaan Alat Hiking Terpercaya</span>
  <h1 class="mt-3">Mendaki Lebih Ringan,<br>Tanpa Perlu Beli Alat Mahal</h1>
  <p class="lead col-lg-6 mt-3">Sewa tenda, carrier, sleeping bag, dan perlengkapan gunung lainnya secara online. Bersih, lengkap, dan siap berangkat.</p>
  <div class="d-flex flex-wrap gap-2 mt-4">
    <a href="<?= url('pelanggan/katalog.php') ?>" class="btn btn-orange btn-lg px-4"><i class="bi bi-grid"></i> Lihat Katalog</a>
    <a href="<?= url('pelanggan/katalog.php') ?>#daftar-alat" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-bag-check"></i> Sewa Sekarang</a>
  </div>
</div></section>

<section class="container py-5">
  <h3 class="section-title mb-4">Kategori Alat</h3>
  <div class="row g-3">
    <?php foreach ($kats as $k): ?>
      <div class="col-6 col-md-4 col-lg-3"><a href="<?= url('pelanggan/katalog.php?kategori=' . $k['id_kategori']) ?>" class="card card-soft text-decoration-none p-3 h-100">
        <div class="fw-semibold"><?= e($k['nama_kategori']) ?></div><small class="text-muted"><?= $k['jml'] ?> jenis alat</small></a></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container py-4">
  <div class="d-flex justify-content-between align-items-end mb-4"><h3 class="section-title mb-0">Produk Unggulan</h3>
    <a href="<?= url('pelanggan/katalog.php') ?>" class="btn btn-outline-forest btn-sm">Semua Alat <i class="bi bi-arrow-right"></i></a></div>
  <div class="row g-4"><?php foreach ($unggulan as $a) echo product_card($a); ?></div>
</section>

<section class="container py-5">
  <h3 class="section-title mb-4">Kenapa Memilih HikeRent?</h3>
  <div class="row g-3">
    <?php foreach ([['bi-shield-check', 'Alat Terawat', 'Setiap alat dicek dan dibersihkan sebelum disewakan.'], ['bi-cash-coin', 'Harga Terjangkau', 'Harga sewa per hari yang ramah di kantong.'],
      ['bi-calendar-check', 'Booking Mudah', 'Pilih alat, tentukan tanggal, dan checkout dalam hitungan menit.'], ['bi-headset', 'Layanan Responsif', 'Admin siap membantu sebelum, saat, dan sesudah pendakian.']] as $f): ?>
      <div class="col-md-6 col-lg-3"><div class="card card-soft feature h-100"><div><span class="ico"><i class="bi <?= $f[0] ?>"></i></span></div>
        <h6 class="fw-semibold"><?= $f[1] ?></h6><p class="small text-muted mb-0"><?= $f[2] ?></p></div></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container"><div class="cta p-5 text-center">
  <h3 class="fw-bold">Siap Berpetualang?</h3><p class="mb-4">Daftar gratis dan mulai sewa perlengkapan hiking favoritmu.</p>
  <?php if (!is_login()): ?><a href="<?= url('auth/register.php') ?>" class="btn btn-orange btn-lg me-2">Daftar Sekarang</a><?php endif; ?>
  <a href="<?= url('pelanggan/katalog.php') ?>" class="btn btn-outline-light btn-lg">Lihat Katalog</a>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
