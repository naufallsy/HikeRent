<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$q = trim($_GET['q'] ?? '');
$rows = db_all("SELECT u.*, (SELECT COUNT(*) FROM transaksi t WHERE t.id_user=u.id_user) AS jml_trx FROM users u
    WHERE u.role='pelanggan' AND (u.nama LIKE ? OR u.email LIKE ? OR u.no_telp LIKE ?) ORDER BY u.id_user DESC", ["%$q%", "%$q%", "%$q%"]);
$pageTitle = 'Data Pelanggan'; $adminNav = 'pelanggan';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="card card-soft p-3">
  <form class="d-flex gap-2 mb-3" method="get" style="max-width:420px"><input name="q" class="form-control" placeholder="Cari nama / email / telepon" value="<?= e($q) ?>"><button class="btn btn-outline-forest"><i class="bi bi-search"></i></button></form>
  <div class="table-responsive"><table class="table align-middle table-hover"><thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Telepon</th><th>Alamat</th><th>Transaksi</th><th>Terdaftar</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $u): ?><tr><td><?= $i + 1 ?></td><td class="fw-semibold"><?= e($u['nama']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['no_telp']) ?></td>
    <td class="small"><?= e($u['alamat']) ?></td><td><?= $u['jml_trx'] ?></td><td class="small"><?= tgl($u['created_at']) ?></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada pelanggan.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
