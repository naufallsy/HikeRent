<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$jenis = $_GET['jenis'] ?? 'penyewaan';
$dari = valid_date($_GET['dari'] ?? '') ? $_GET['dari'] : date('Y-m-01');
$sampai = valid_date($_GET['sampai'] ?? '') ? $_GET['sampai'] : date('Y-m-t');
$jenisList = ['penyewaan' => 'Laporan Penyewaan', 'pendapatan' => 'Laporan Pendapatan', 'stok' => 'Laporan Stok Barang', 'pengembalian' => 'Laporan Pengembalian', 'transaksi' => 'Laporan Transaksi per Periode'];
if (!isset($jenisList[$jenis])) $jenis = 'penyewaan';
$rows = []; $summary = '';
if ($jenis === 'penyewaan') {
    $rows = db_all("SELECT t.kode_transaksi, t.nama_penyewa, t.tgl_ambil, t.tgl_kembali, t.durasi, t.status, a.nama_alat, d.jumlah, d.harga_satuan, d.subtotal
        FROM detail_transaksi d JOIN transaksi t ON t.id_transaksi=d.id_transaksi JOIN alat a ON a.id_alat=d.id_alat
        WHERE DATE(t.created_at) BETWEEN ? AND ? AND t.status NOT IN ('ditolak','dibatalkan') ORDER BY t.created_at, t.id_transaksi", [$dari, $sampai]);
    $summary = 'Total nilai sewa: ' . rupiah(array_sum(array_column($rows, 'subtotal'))) . ' dari ' . count($rows) . ' item.';
} elseif ($jenis === 'pendapatan') {
    $rows = db_all("SELECT t.kode_transaksi, t.nama_penyewa, DATE(t.created_at) AS tanggal, p.metode, p.jumlah AS biaya_sewa, COALESCE(r.total_denda,0) AS denda
        FROM transaksi t JOIN pembayaran p ON p.id_transaksi=t.id_transaksi LEFT JOIN pengembalian r ON r.id_transaksi=t.id_transaksi
        WHERE p.status='berhasil' AND t.status NOT IN ('ditolak','dibatalkan') AND DATE(t.created_at) BETWEEN ? AND ? ORDER BY t.created_at", [$dari, $sampai]);
    $summary = 'Total pendapatan: ' . rupiah(array_sum(array_column($rows, 'biaya_sewa')) + array_sum(array_column($rows, 'denda'))) . ' (sewa ' . rupiah(array_sum(array_column($rows, 'biaya_sewa'))) . ' + denda ' . rupiah(array_sum(array_column($rows, 'denda'))) . ').';
} elseif ($jenis === 'stok') {
    $rows = db_all("SELECT a.id_alat, a.nama_alat, k.nama_kategori, a.harga_sewa, a.stok, a.status FROM alat a JOIN kategori k ON k.id_kategori=a.id_kategori ORDER BY k.nama_kategori, a.nama_alat");
    foreach ($rows as &$r) { $r['tersedia'] = stok_tersedia($r['id_alat']); $r['terpakai'] = $r['stok'] - $r['tersedia']; } unset($r);
    $summary = 'Posisi stok per tanggal ' . tgl(date('Y-m-d')) . '. Total unit: ' . array_sum(array_column($rows, 'stok')) . '.';
} elseif ($jenis === 'pengembalian') {
    $rows = db_all("SELECT r.*, t.kode_transaksi, t.nama_penyewa, t.tgl_kembali FROM pengembalian r JOIN transaksi t ON t.id_transaksi=r.id_transaksi WHERE r.tgl_dikembalikan BETWEEN ? AND ? ORDER BY r.tgl_dikembalikan", [$dari, $sampai]);
    $summary = 'Total pengembalian: ' . count($rows) . ' | Total denda: ' . rupiah(array_sum(array_column($rows, 'total_denda'))) . '.';
} else {
    $rows = db_all("SELECT t.*, p.status AS status_bayar FROM transaksi t LEFT JOIN pembayaran p ON p.id_transaksi=t.id_transaksi WHERE DATE(t.created_at) BETWEEN ? AND ? ORDER BY t.created_at", [$dari, $sampai]);
    $summary = 'Jumlah transaksi: ' . count($rows) . ' | Total nilai: ' . rupiah(array_sum(array_column($rows, 'total_biaya'))) . '.';
}
$pageTitle = 'Laporan'; $adminNav = 'laporan';
include __DIR__ . '/../../includes/admin_header.php';
?>
<form class="card card-soft p-3 mb-4 no-print" method="get"><div class="row g-2 align-items-end">
  <div class="col-md-4"><label class="form-label small mb-1">Jenis Laporan</label><select name="jenis" class="form-select"><?php foreach ($jenisList as $k => $l): ?><option value="<?= $k ?>" <?= $jenis === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-1">Dari</label><input type="date" name="dari" class="form-control" value="<?= $dari ?>"></div>
  <div class="col-6 col-md-2"><label class="form-label small mb-1">Sampai</label><input type="date" name="sampai" class="form-control" value="<?= $sampai ?>"></div>
  <div class="col-auto"><button class="btn btn-forest"><i class="bi bi-funnel"></i> Tampilkan</button></div>
  <div class="col-auto"><button type="button" class="btn btn-orange" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button></div></div></form>
<div class="card card-soft p-4">
  <div class="text-center mb-3"><h5 class="fw-bold mb-0">HikeRent – Penyewaan Alat Hiking</h5><div><?= $jenisList[$jenis] ?></div>
    <?php if ($jenis !== 'stok'): ?><small class="text-muted">Periode <?= tgl($dari) ?> s/d <?= tgl($sampai) ?></small><?php endif; ?></div>
  <div class="table-responsive"><table class="table table-bordered table-sm align-middle"><thead>
  <?php if ($jenis === 'penyewaan'): ?><tr><th>No</th><th>Kode</th><th>Penyewa</th><th>Tgl Sewa</th><th>Alat</th><th>Jml</th><th>Harga/hari</th><th>Durasi</th><th>Subtotal</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['kode_transaksi']) ?></td><td><?= e($r['nama_penyewa']) ?></td><td><?= tgl($r['tgl_ambil']) ?> – <?= tgl($r['tgl_kembali']) ?></td><td><?= e($r['nama_alat']) ?></td><td><?= $r['jumlah'] ?></td><td><?= rupiah($r['harga_satuan']) ?></td><td><?= $r['durasi'] ?> hr</td><td><?= rupiah($r['subtotal']) ?></td><td><?= badge($r['status']) ?></td></tr><?php endforeach; ?>
  <?php elseif ($jenis === 'pendapatan'): ?><tr><th>No</th><th>Tanggal</th><th>Kode</th><th>Penyewa</th><th>Metode</th><th>Biaya Sewa</th><th>Denda</th><th>Total</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= tgl($r['tanggal']) ?></td><td><?= e($r['kode_transaksi']) ?></td><td><?= e($r['nama_penyewa']) ?></td><td><?= e($r['metode']) ?></td><td><?= rupiah($r['biaya_sewa']) ?></td><td><?= rupiah($r['denda']) ?></td><td class="fw-semibold"><?= rupiah($r['biaya_sewa'] + $r['denda']) ?></td></tr><?php endforeach; ?>
  <?php elseif ($jenis === 'stok'): ?><tr><th>No</th><th>Nama Alat</th><th>Kategori</th><th>Harga/hari</th><th>Stok Total</th><th>Terpakai/Dipesan</th><th>Tersedia</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['nama_alat']) ?></td><td><?= e($r['nama_kategori']) ?></td><td><?= rupiah($r['harga_sewa']) ?></td><td><?= $r['stok'] ?></td><td><?= $r['terpakai'] ?></td><td><?= $r['tersedia'] ?></td><td><?= badge($r['status']) ?></td></tr><?php endforeach; ?>
  <?php elseif ($jenis === 'pengembalian'): ?><tr><th>No</th><th>Kode</th><th>Penyewa</th><th>Jatuh Tempo</th><th>Dikembalikan</th><th>Kondisi</th><th>Terlambat</th><th>Total Denda</th><th>Catatan</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['kode_transaksi']) ?></td><td><?= e($r['nama_penyewa']) ?></td><td><?= tgl($r['tgl_kembali']) ?></td><td><?= tgl($r['tgl_dikembalikan']) ?></td><td><?= badge($r['kondisi']) ?></td><td><?= $r['hari_terlambat'] ?> hr</td><td><?= rupiah($r['total_denda']) ?></td><td><?= e($r['catatan']) ?></td></tr><?php endforeach; ?>
  <?php else: ?><tr><th>No</th><th>Kode</th><th>Tanggal Transaksi</th><th>Penyewa</th><th>Tgl Sewa</th><th>Total</th><th>Status</th><th>Pembayaran</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= e($r['kode_transaksi']) ?></td><td><?= tgl($r['created_at']) ?></td><td><?= e($r['nama_penyewa']) ?></td><td><?= tgl($r['tgl_ambil']) ?> – <?= tgl($r['tgl_kembali']) ?></td><td><?= rupiah($r['total_biaya']) ?></td><td><?= badge($r['status']) ?></td><td><?= badge($r['status_bayar']) ?></td></tr><?php endforeach; ?>
  <?php endif; ?>
  <?php if (!$rows): ?><tr><td colspan="10" class="text-center text-muted py-3">Tidak ada data pada periode ini.</td></tr><?php endif; ?>
  </tbody></table></div>
  <p class="fw-semibold mb-0"><?= e($summary) ?></p><p class="small text-muted mb-0">Dicetak: <?= tgl(date('Y-m-d')) ?> oleh <?= e(user()['nama']) ?></p>
</div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
