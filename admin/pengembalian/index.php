<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$aktif = db_all("SELECT t.*, (SELECT COALESCE(SUM(harga_satuan*jumlah),0) FROM detail_transaksi WHERE id_transaksi=t.id_transaksi) AS per_hari FROM transaksi t WHERE t.status='diambil' ORDER BY t.tgl_kembali ASC");
$riwayat = db_all("SELECT r.*, t.kode_transaksi, t.nama_penyewa, t.tgl_kembali FROM pengembalian r JOIN transaksi t ON t.id_transaksi=r.id_transaksi ORDER BY r.id_pengembalian DESC");
$pageTitle = 'Manajemen Pengembalian'; $adminNav = 'pengembalian';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="card card-soft p-3 mb-4"><h6 class="fw-bold">Menunggu Pengembalian (<?= count($aktif) ?>)</h6>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Kode</th><th>Penyewa</th><th>Jatuh Tempo</th><th>Keterangan</th><th></th></tr></thead><tbody>
  <?php foreach ($aktif as $t): $telat = max(0, (int)(new DateTime($t['tgl_kembali']))->diff(new DateTime('today'))->format('%r%a')); ?>
    <tr><td class="fw-semibold"><?= e($t['kode_transaksi']) ?></td><td><?= e($t['nama_penyewa']) ?><br><small class="text-muted"><?= e($t['no_telp']) ?></small></td><td><?= tgl($t['tgl_kembali']) ?></td>
      <td><?= $telat > 0 ? '<span class="badge text-bg-danger">Terlambat ' . $telat . ' hari</span>' : '<span class="badge text-bg-success">Masih dalam masa sewa</span>' ?></td>
      <td><a class="btn btn-sm btn-forest" href="<?= url('admin/pengembalian/proses.php?id=' . $t['id_transaksi']) ?>">Proses Pengembalian</a></td></tr><?php endforeach; ?>
  <?php if (!$aktif): ?><tr><td colspan="5" class="text-center text-muted py-3">Tidak ada alat yang sedang disewa.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<div class="card card-soft p-3"><h6 class="fw-bold">Riwayat Pengembalian</h6>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Kode</th><th>Penyewa</th><th>Jatuh Tempo</th><th>Dikembalikan</th><th>Kondisi</th><th>Terlambat</th><th>Total Denda</th><th>Catatan</th></tr></thead><tbody>
  <?php foreach ($riwayat as $r): ?><tr><td><a href="<?= url('admin/transaksi/detail.php?id=' . $r['id_transaksi']) ?>"><?= e($r['kode_transaksi']) ?></a></td><td><?= e($r['nama_penyewa']) ?></td><td><?= tgl($r['tgl_kembali']) ?></td>
    <td><?= tgl($r['tgl_dikembalikan']) ?></td><td><?= badge($r['kondisi']) ?></td><td><?= $r['hari_terlambat'] ?> hari</td><td><?= rupiah($r['total_denda']) ?></td><td class="small"><?= e($r['catatan']) ?></td></tr><?php endforeach; ?>
  <?php if (!$riwayat): ?><tr><td colspan="8" class="text-center text-muted py-3">Belum ada data pengembalian.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
