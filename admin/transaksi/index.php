<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$st = $_GET['status'] ?? ''; $q = trim($_GET['q'] ?? '');
$where = ['(t.kode_transaksi LIKE ? OR t.nama_penyewa LIKE ?)']; $p = ["%$q%", "%$q%"];
if (in_array($st, ['menunggu', 'dikonfirmasi', 'diambil', 'selesai', 'ditolak', 'dibatalkan'])) { $where[] = 't.status=?'; $p[] = $st; }
$rows = db_all("SELECT t.*, p.status AS status_bayar FROM transaksi t LEFT JOIN pembayaran p ON p.id_transaksi=t.id_transaksi WHERE " . implode(' AND ', $where) . " ORDER BY t.id_transaksi DESC", $p);
$pageTitle = 'Manajemen Transaksi'; $adminNav = 'transaksi';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="card card-soft p-3">
  <form class="row g-2 mb-3" method="get"><div class="col-md-4"><input name="q" class="form-control" placeholder="Cari kode / nama penyewa" value="<?= e($q) ?>"></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Semua status</option>
      <?php foreach (['menunggu', 'dikonfirmasi', 'diambil', 'selesai', 'ditolak', 'dibatalkan'] as $s): ?><option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-auto"><button class="btn btn-forest">Filter</button></div></form>
  <div class="table-responsive"><table class="table align-middle table-hover"><thead><tr><th>Kode</th><th>Penyewa</th><th>Tgl Sewa</th><th>Total</th><th>Status</th><th>Bayar</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $t): ?><tr><td class="fw-semibold"><?= e($t['kode_transaksi']) ?><br><small class="text-muted"><?= tgl($t['created_at']) ?></small></td><td><?= e($t['nama_penyewa']) ?><br><small class="text-muted"><?= e($t['no_telp']) ?></small></td>
    <td class="small"><?= tgl($t['tgl_ambil']) ?> – <?= tgl($t['tgl_kembali']) ?><br><span class="text-muted"><?= $t['durasi'] ?> hari</span></td><td><?= rupiah($t['total_biaya']) ?></td>
    <td><?= badge($t['status']) ?></td><td><?= badge($t['status_bayar']) ?></td>
    <td><a class="btn btn-sm btn-outline-forest" href="<?= url('admin/transaksi/detail.php?id=' . $t['id_transaksi']) ?>">Detail</a></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
