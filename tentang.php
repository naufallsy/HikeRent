<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tentang Kami'; $nav = 'tentang';
$stat = ['alat' => db_val("SELECT COUNT(*) FROM alat"), 'pelanggan' => db_val("SELECT COUNT(*) FROM users WHERE role='pelanggan'"), 'transaksi' => db_val("SELECT COUNT(*) FROM transaksi")];
include __DIR__ . '/includes/header.php';
?>
<section class="page-head"><div class="container"><h2 class="fw-bold mb-1">Tentang HikeRent</h2><p class="mb-0 opacity-75">Teman terbaik untuk setiap pendakianmu</p></div></section>
<div class="container py-5"><div class="row g-4 align-items-center">
  <div class="col-lg-6"><img class="detail-img" src="<?= url('assets/images/hero.svg') ?>" alt="Pegunungan"></div>
  <div class="col-lg-6"><h3 class="section-title mb-3">Cerita Kami</h3>
    <p>HikeRent hadir untuk memudahkan para pendaki mendapatkan perlengkapan berkualitas tanpa harus membeli. Dengan sistem penyewaan online, kamu dapat memilih alat, menentukan tanggal, dan membayar dari rumah.</p>
    <p>Seluruh alat kami dibersihkan dan diperiksa setiap selesai disewa, sehingga siap dipakai untuk pendakian berikutnya.</p>
    <div class="row text-center g-3 mt-2">
      <div class="col-4"><div class="card card-soft p-3"><h4 class="text-orange mb-0" style="color:var(--orange)"><?= $stat['alat'] ?></h4><small>Jenis Alat</small></div></div>
      <div class="col-4"><div class="card card-soft p-3"><h4 class="mb-0" style="color:var(--orange)"><?= $stat['pelanggan'] ?></h4><small>Pelanggan</small></div></div>
      <div class="col-4"><div class="card card-soft p-3"><h4 class="mb-0" style="color:var(--orange)"><?= $stat['transaksi'] ?></h4><small>Transaksi</small></div></div>
    </div></div></div>
  <h3 class="section-title mt-5 mb-4">Alur Penyewaan</h3>
  <div class="row g-3">
    <?php foreach (['Pilih alat di katalog', 'Masukkan ke keranjang & tentukan tanggal', 'Checkout dan upload bukti bayar', 'Admin konfirmasi, ambil alat sesuai jadwal', 'Kembalikan alat, admin cek kondisi'] as $i => $s): ?>
      <div class="col-md-6 col-lg"><div class="card card-soft p-3 h-100 text-center"><div><span class="step-no"><?= $i + 1 ?></span></div><p class="small mb-0 mt-2"><?= $s ?></p></div></div>
    <?php endforeach; ?>
  </div>
  <h3 class="section-title mt-5 mb-3">Ketentuan Penyewaan</h3>
  <ul><li>Penyewa wajib membawa identitas (KTP/SIM) saat pengambilan alat.</li><li>Durasi sewa dihitung dari tanggal pengambilan hingga tanggal pengembalian (minimal 1 hari).</li>
    <li>Keterlambatan dikenakan denda <?= DENDA_PERSEN ?>% dari total biaya sewa per hari.</li><li>Kerusakan atau kehilangan alat dikenakan ganti rugi sesuai penilaian admin.</li></ul>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
