<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $id = (int)($_POST['id_alat'] ?? 0);
    if (($_POST['action'] ?? '') === 'hapus') {
        $a = db_row("SELECT * FROM alat WHERE id_alat=?", [$id]);
        if (!$a) flash('danger', 'Data alat tidak ditemukan.');
        elseif (db_val("SELECT COUNT(*) FROM detail_transaksi WHERE id_alat=?", [$id])) flash('warning', 'Alat sudah pernah disewa sehingga tidak bisa dihapus. Ubah status menjadi "Tidak Tersedia" saja.');
        else {
            db_run("DELETE FROM alat WHERE id_alat=?", [$id]);
            if ($a['foto'] && !preg_match('/\.svg$/', $a['foto']) && is_file(ROOT_PATH . '/assets/images/alat/' . $a['foto'])) @unlink(ROOT_PATH . '/assets/images/alat/' . $a['foto']);
            flash('success', 'Data alat "' . $a['nama_alat'] . '" berhasil dihapus.');
        }
    }
    redirect('admin/alat/index.php');
}
$q = trim($_GET['q'] ?? '');
$rows = db_all("SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON k.id_kategori=a.id_kategori WHERE a.nama_alat LIKE ? OR k.nama_kategori LIKE ? ORDER BY a.id_alat DESC", ["%$q%", "%$q%"]);
$pageTitle = 'Data Alat'; $adminNav = 'alat';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="card card-soft p-3">
  <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form class="d-flex gap-2" method="get"><input name="q" class="form-control" placeholder="Cari alat / kategori" value="<?= e($q) ?>"><button class="btn btn-outline-forest"><i class="bi bi-search"></i></button></form>
    <a href="<?= url('admin/alat/form.php') ?>" class="btn btn-forest"><i class="bi bi-plus-lg"></i> Tambah Alat</a></div>
  <div class="table-responsive"><table class="table align-middle table-hover"><thead><tr><th>Foto</th><th>Nama Alat</th><th>Kategori</th><th>Harga/hari</th><th>Stok Total</th><th>Tersedia Hari Ini</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
  <?php foreach ($rows as $a): ?><tr>
    <td><img src="<?= foto_url($a['foto']) ?>" class="cart-img" alt=""></td><td class="fw-semibold"><?= e($a['nama_alat']) ?></td><td><?= e($a['nama_kategori']) ?></td>
    <td><?= rupiah($a['harga_sewa']) ?></td><td><?= $a['stok'] ?></td><td><?= stok_tersedia($a['id_alat']) ?></td><td><?= badge($a['status']) ?></td>
    <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/alat/form.php?id=' . $a['id_alat']) ?>"><i class="bi bi-pencil"></i></a>
      <form method="post" class="d-inline" data-confirm="Hapus alat '<?= e($a['nama_alat']) ?>'?"><?= csrf_field() ?><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_alat" value="<?= $a['id_alat'] ?>">
      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Data tidak ditemukan.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
