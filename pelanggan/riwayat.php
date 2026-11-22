<?php
require_once __DIR__ . '/../includes/functions.php';
require_pelanggan();
$uid = user()['id'];
$rows = db_all("SELECT t.*, p.status AS status_bayar, r.id_pengembalian, r.total_denda,
   (SELECT GROUP_CONCAT(CONCAT(a.nama_alat,' ×',d.jumlah) SEPARATOR ', ') FROM detail_transaksi d JOIN alat a ON a.id_alat=d.id_alat WHERE d.id_transaksi=t.id_transaksi) AS items
   FROM transaksi t LEFT JOIN pembayaran p ON p.id_transaksi=t.id_transaksi LEFT JOIN pengembalian r ON r.id_transaksi=t.id_transaksi
   WHERE t.id_user=? ORDER BY t.id_transaksi DESC", [$uid]);
$pageTitle = 'Riwayat Penyewaan'; $nav = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h2 class="fw-bold mb-0"><i class="bi bi-clock-history"></i> Riwayat Penyewaan</h2></div></section>
<div class="container py-4"><div class="card card-soft"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead><tr><th>Kode</th><th>Tanggal Sewa</th><th>Barang</th><th>Total</th><th>Penyewaan</th><th>Pembayaran</th><th>Pengembalian</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?><tr>
    <td class="fw-semibold"><?= e($r['kode_transaksi']) ?><br><small class="text-muted"><?= tgl($r['created_at']) ?></small></td>
    <td class="small"><?= tgl($r['tgl_ambil']) ?> – <?= tgl($r['tgl_kembali']) ?><br><span class="text-muted"><?= $r['durasi'] ?> hari</span></td>
    <td class="small" style="max-width:240px"><?= e($r['items']) ?></td>
    <td><?= rupiah($r['total_biaya']) ?><?= $r['total_denda'] ? '<br><small class="text-danger">+ denda ' . rupiah($r['total_denda']) . '</small>' : '' ?></td>
    <td><?= badge($r['status']) ?></td><td><?= badge($r['status_bayar']) ?></td><td><?= status_kembali($r['status'], $r['id_pengembalian']) ?></td>
    <td><a class="btn btn-sm btn-outline-forest" href="<?= url('pelanggan/transaksi.php?id=' . $r['id_transaksi']) ?>">Detail</a></td></tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8" class="text-center py-5 text-muted">Belum ada transaksi. <a href="<?= url('pelanggan/katalog.php') ?>">Mulai menyewa</a></td></tr><?php endif; ?>
  </tbody></table></div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
