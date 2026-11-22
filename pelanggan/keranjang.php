<?php
require_once __DIR__ . '/../includes/functions.php';
require_pelanggan();
$uid = user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $act = $_POST['action'] ?? '';
    if ($act === 'tambah') {
        $id = (int)($_POST['id_alat'] ?? 0); $jml = (int)($_POST['jumlah'] ?? 1);
        $a = db_row("SELECT * FROM alat WHERE id_alat=?", [$id]);
        if ($jml < 1) flash('warning', 'Jumlah minimal 1.');
        elseif (!$a || $a['status'] !== 'tersedia') flash('danger', 'Alat tidak tersedia.');
        else {
            $baru = (int)db_val("SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE id_user=? AND id_alat=?", [$uid, $id]) + $jml;
            if ($baru > $a['stok']) flash('warning', 'Jumlah melebihi stok (' . $a['stok'] . ' unit).');
            else {
                db_run("INSERT INTO keranjang (id_user,id_alat,jumlah) VALUES (?,?,?) ON DUPLICATE KEY UPDATE jumlah=?", [$uid, $id, $jml, $baru]);
                flash('success', '"' . $a['nama_alat'] . '" berhasil ditambahkan ke keranjang.');
            }
        }
        redirect(safe_back($_POST['back'] ?? '', 'pelanggan/keranjang.php'));
    }
    if ($act === 'ubah') {
        $idk = (int)($_POST['id_keranjang'] ?? 0); $jml = (int)($_POST['jumlah'] ?? 0);
        $c = db_row("SELECT k.*, a.stok FROM keranjang k JOIN alat a ON a.id_alat=k.id_alat WHERE k.id_keranjang=? AND k.id_user=?", [$idk, $uid]);
        if ($c) {
            if ($jml < 1) { db_run("DELETE FROM keranjang WHERE id_keranjang=? AND id_user=?", [$idk, $uid]); flash('success', 'Barang dihapus dari keranjang.'); }
            elseif ($jml > $c['stok']) flash('warning', 'Jumlah melebihi stok (' . $c['stok'] . ' unit).');
            else { db_run("UPDATE keranjang SET jumlah=? WHERE id_keranjang=? AND id_user=?", [$jml, $idk, $uid]); flash('success', 'Jumlah barang diperbarui.'); }
        }
        redirect('pelanggan/keranjang.php');
    }
    if ($act === 'hapus') {
        db_run("DELETE FROM keranjang WHERE id_keranjang=? AND id_user=?", [(int)($_POST['id_keranjang'] ?? 0), $uid]);
        flash('success', 'Barang dihapus dari keranjang.'); redirect('pelanggan/keranjang.php');
    }
}
$rows = db_all("SELECT k.*, a.nama_alat, a.harga_sewa, a.stok, a.foto, a.status FROM keranjang k JOIN alat a ON a.id_alat=k.id_alat WHERE k.id_user=? ORDER BY k.id_keranjang DESC", [$uid]);
$perHari = 0; foreach ($rows as $r) $perHari += $r['harga_sewa'] * $r['jumlah'];
$pageTitle = 'Keranjang Penyewaan'; $nav = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h2 class="fw-bold mb-0"><i class="bi bi-cart3"></i> Keranjang Penyewaan</h2></div></section>
<div class="container py-4">
<?php if (!$rows): ?>
  <div class="card card-soft p-5 text-center"><i class="bi bi-cart-x display-4 text-muted"></i><h5 class="mt-3">Keranjang masih kosong</h5>
    <p class="text-muted">Yuk pilih perlengkapan hiking di katalog.</p><div><a href="<?= url('pelanggan/katalog.php') ?>" class="btn btn-forest">Lihat Katalog</a></div></div>
<?php else: ?>
<div class="row g-4">
  <div class="col-lg-8"><div class="card card-soft"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Alat</th><th>Harga/hari</th><th style="width:130px">Jumlah</th><th>Subtotal/hari</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr>
      <td><div class="d-flex align-items-center gap-2"><img class="cart-img" src="<?= foto_url($r['foto']) ?>" alt=""><div><div class="fw-semibold"><?= e($r['nama_alat']) ?></div>
        <small class="text-muted">Stok: <?= $r['stok'] ?></small></div></div></td>
      <td><?= rupiah($r['harga_sewa']) ?></td>
      <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="ubah"><input type="hidden" name="id_keranjang" value="<?= $r['id_keranjang'] ?>">
        <input type="number" name="jumlah" min="0" max="<?= $r['stok'] ?>" value="<?= $r['jumlah'] ?>" class="form-control form-control-sm" data-autosubmit></form></td>
      <td class="fw-semibold"><?= rupiah($r['harga_sewa'] * $r['jumlah']) ?></td>
      <td><form method="post" data-confirm="Hapus barang ini dari keranjang?"><?= csrf_field() ?><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_keranjang" value="<?= $r['id_keranjang'] ?>">
        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
    </tr><?php endforeach; ?></tbody></table></div></div></div>
  <div class="col-lg-4"><form method="get" action="<?= url('pelanggan/checkout.php') ?>" class="card card-soft p-3" data-perhari="<?= $perHari ?>">
    <h6 class="fw-bold">Ringkasan Sewa</h6>
    <div class="mb-2"><label class="form-label small mb-1">Tanggal Pengambilan</label><input type="date" id="tgl_ambil" name="tgl_ambil" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required></div>
    <div class="mb-3"><label class="form-label small mb-1">Tanggal Pengembalian</label><input type="date" id="tgl_kembali" name="tgl_kembali" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+2 days')) ?>" required></div>
    <div class="d-flex justify-content-between small"><span>Total per hari</span><span><?= rupiah($perHari) ?></span></div>
    <div class="d-flex justify-content-between small"><span>Durasi</span><span id="durasi">1 hari</span></div><hr>
    <div class="d-flex justify-content-between fw-bold fs-5"><span>Total</span><span id="total" class="price"><?= rupiah($perHari) ?></span></div>
    <button class="btn btn-orange w-100 mt-3">Lanjut ke Checkout <i class="bi bi-arrow-right"></i></button>
  </form></div>
</div>
<?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
