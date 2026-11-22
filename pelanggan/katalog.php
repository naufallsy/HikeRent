<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Katalog Alat'; $nav = 'katalog';
$q = trim($_GET['q'] ?? ''); $kat = (int)($_GET['kategori'] ?? 0); $sort = $_GET['sort'] ?? 'terbaru';
$min = ($_GET['min'] ?? '') !== '' ? max(0, (int)$_GET['min']) : null; $max = ($_GET['max'] ?? '') !== '' ? max(0, (int)$_GET['max']) : null;
$where = ['1=1']; $p = [];
if ($q !== '') { $where[] = '(a.nama_alat LIKE ? OR a.deskripsi LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
if ($kat) { $where[] = 'a.id_kategori=?'; $p[] = $kat; }
if ($min !== null) { $where[] = 'a.harga_sewa>=?'; $p[] = $min; }
if ($max !== null) { $where[] = 'a.harga_sewa<=?'; $p[] = $max; }
$order = ['terbaru' => 'a.id_alat DESC', 'termurah' => 'a.harga_sewa ASC', 'termahal' => 'a.harga_sewa DESC', 'nama' => 'a.nama_alat ASC'][$sort] ?? 'a.id_alat DESC';
$items = db_all("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON k.id_kategori=a.id_kategori WHERE " . implode(' AND ', $where) . " ORDER BY $order", $p);
$kats = db_all("SELECT * FROM kategori ORDER BY nama_kategori");
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h2 class="fw-bold mb-1">Katalog Alat Hiking</h2><p class="mb-0 opacity-75">Temukan perlengkapan yang kamu butuhkan</p></div></section>
<div class="container py-4" id="daftar-alat">
  <form class="card card-soft p-3 mb-4" method="get"><div class="row g-2 align-items-end">
    <div class="col-lg-3"><label class="form-label small mb-1">Cari alat</label><input name="q" class="form-control" placeholder="Contoh: tenda" value="<?= e($q) ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label small mb-1">Kategori</label><select name="kategori" class="form-select"><option value="0">Semua</option>
      <?php foreach ($kats as $k): ?><option value="<?= $k['id_kategori'] ?>" <?= $kat == $k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option><?php endforeach; ?></select></div>
    <div class="col-3 col-lg-1"><label class="form-label small mb-1">Harga min</label><input type="number" min="0" name="min" class="form-control" value="<?= $min ?>"></div>
    <div class="col-3 col-lg-1"><label class="form-label small mb-1">Harga max</label><input type="number" min="0" name="max" class="form-control" value="<?= $max ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label small mb-1">Urutkan</label><select name="sort" class="form-select">
      <?php foreach (['terbaru' => 'Terbaru', 'termurah' => 'Harga termurah', 'termahal' => 'Harga termahal', 'nama' => 'Nama A-Z'] as $v => $l): ?><option value="<?= $v ?>" <?= $sort === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div class="col-6 col-lg-3 d-flex gap-2"><button class="btn btn-forest flex-fill"><i class="bi bi-search"></i> Cari</button><a href="<?= url('pelanggan/katalog.php') ?>" class="btn btn-outline-secondary">Reset</a></div>
  </div></form>
  <p class="text-muted small">Menampilkan <?= count($items) ?> alat</p>
  <div class="row g-4">
    <?php foreach ($items as $a) echo product_card($a); ?>
    <?php if (!$items): ?><div class="col-12"><div class="alert alert-warning">Tidak ada alat yang cocok dengan pencarian Anda.</div></div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
