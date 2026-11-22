<?php
require_once __DIR__ . '/../includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$a = db_row("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.id_alat=?", [$id]);
if (!$a) { flash('danger', 'Alat tidak ditemukan.'); redirect('pelanggan/katalog.php'); }
$pageTitle = $a['nama_alat']; $nav = 'katalog';
$sisa = stok_tersedia($id);
// Cek ketersediaan pada tanggal tertentu
$cek = null; $ta = $_GET['tgl_ambil'] ?? ''; $tk = $_GET['tgl_kembali'] ?? '';
if ($ta !== '' && $tk !== '') {
    if (valid_date($ta) && valid_date($tk) && $tk >= $ta && $ta >= date('Y-m-d')) $cek = stok_tersedia($id, $ta, $tk);
    else flash('warning', 'Rentang tanggal tidak valid.');
}
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <nav class="small mb-3"><a href="<?= url('index.php') ?>">Beranda</a> / <a href="<?= url('pelanggan/katalog.php') ?>">Katalog</a> / <?= e($a['nama_alat']) ?></nav>
  <div class="row g-4">
    <div class="col-lg-6"><img class="detail-img shadow-sm" src="<?= foto_url($a['foto']) ?>" alt="<?= e($a['nama_alat']) ?>"></div>
    <div class="col-lg-6">
      <span class="badge text-bg-secondary mb-2"><?= e($a['nama_kategori']) ?></span> <?= badge($sisa > 0 ? 'tersedia' : 'tidak_tersedia') ?>
      <h2 class="fw-bold"><?= e($a['nama_alat']) ?></h2>
      <div class="fs-3 price mb-2"><?= rupiah($a['harga_sewa']) ?><small>/hari</small></div>
      <p><?= nl2br(e($a['deskripsi'])) ?></p>
      <p class="mb-1"><b>Stok total:</b> <?= $a['stok'] ?> unit &nbsp;|&nbsp; <b>Tersedia hari ini:</b> <?= $sisa ?> unit</p>
      <form method="get" class="card card-soft p-3 my-3"><input type="hidden" name="id" value="<?= $id ?>">
        <label class="form-label small fw-semibold">Cek ketersediaan pada tanggal</label>
        <div class="row g-2"><div class="col"><input type="date" name="tgl_ambil" min="<?= date('Y-m-d') ?>" class="form-control" value="<?= e($ta) ?>" required></div>
          <div class="col"><input type="date" name="tgl_kembali" min="<?= date('Y-m-d') ?>" class="form-control" value="<?= e($tk) ?>" required></div>
          <div class="col-auto"><button class="btn btn-outline-forest">Cek</button></div></div>
        <?php if ($cek !== null): ?><div class="mt-2 small <?= $cek > 0 ? 'text-success' : 'text-danger' ?>"><i class="bi bi-info-circle"></i>
          <?= $cek > 0 ? "Pada tanggal tersebut tersedia $cek unit." : 'Stok pada tanggal tersebut sudah habis.' ?></div><?php endif; ?>
      </form>
      <form method="post" action="<?= url('pelanggan/keranjang.php') ?>" class="d-flex gap-2 align-items-center">
        <?= csrf_field() ?><input type="hidden" name="action" value="tambah"><input type="hidden" name="id_alat" value="<?= $id ?>">
        <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
        <input type="number" name="jumlah" value="1" min="1" max="<?= max(1, $a['stok']) ?>" class="form-control" style="width:90px" <?= $sisa > 0 ? '' : 'disabled' ?>>
        <button class="btn btn-orange px-4" <?= $sisa > 0 ? '' : 'disabled' ?>><i class="bi bi-bag-plus"></i> Tambah ke Keranjang</button>
      </form>
      <div class="card card-soft p-3 mt-4"><h6 class="fw-semibold"><i class="bi bi-file-text"></i> Ketentuan Penyewaan</h6>
        <ul class="small mb-0"><li>Bawa identitas asli (KTP/SIM) saat mengambil alat.</li><li>Durasi sewa minimal 1 hari.</li>
          <li>Denda keterlambatan <?= DENDA_PERSEN ?>% dari total sewa per hari.</li><li>Kerusakan/kehilangan alat menjadi tanggung jawab penyewa.</li></ul></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
