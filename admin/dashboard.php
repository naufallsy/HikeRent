<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pageTitle = 'Dashboard'; $adminNav = 'dashboard';
$s = [
  ['Total Pelanggan', db_val("SELECT COUNT(*) FROM users WHERE role='pelanggan'"), 'bi-people', '#2d5a3d'],
  ['Total Alat Hiking', db_val("SELECT COUNT(*) FROM alat"), 'bi-backpack4', '#3c4f76'],
  ['Total Transaksi', db_val("SELECT COUNT(*) FROM transaksi"), 'bi-receipt', '#8a4b1f'],
  ['Penyewaan Aktif', db_val("SELECT COUNT(*) FROM transaksi WHERE status IN ('dikonfirmasi','diambil')"), 'bi-activity', '#e8772e'],
  ['Total Pendapatan', rupiah(total_pendapatan() + (int)db_val("SELECT COALESCE(SUM(total_denda),0) FROM pengembalian")), 'bi-cash-stack', '#1f7a4d'],
];
// Grafik 6 bulan terakhir
$bulan = []; for ($i = 5; $i >= 0; $i--) $bulan[date('Y-m', strtotime("first day of -$i month"))] = 0;
foreach (db_all("SELECT DATE_FORMAT(created_at,'%Y-%m') AS b, COUNT(*) AS n FROM transaksi WHERE created_at >= ? GROUP BY b", [date('Y-m-01', strtotime('-5 month'))]) as $r)
    if (isset($bulan[$r['b']])) $bulan[$r['b']] = (int)$r['n'];
$maks = max(1, max($bulan));
$nm = ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu', '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'];
$terbaru = db_all("SELECT t.*, p.status AS status_bayar FROM transaksi t LEFT JOIN pembayaran p ON p.id_transaksi=t.id_transaksi ORDER BY t.id_transaksi DESC LIMIT 6");
$perlu = [
  ['Pembayaran menunggu konfirmasi', db_val("SELECT COUNT(*) FROM pembayaran WHERE status='menunggu_konfirmasi'"), 'admin/pembayaran/index.php?status=menunggu_konfirmasi'],
  ['Transaksi menunggu konfirmasi', db_val("SELECT COUNT(*) FROM transaksi WHERE status='menunggu'"), 'admin/transaksi/index.php?status=menunggu'],
  ['Alat belum dikembalikan', db_val("SELECT COUNT(*) FROM transaksi WHERE status='diambil'"), 'admin/pengembalian/index.php'],
];
include __DIR__ . '/../includes/admin_header.php';
?>
<div class="row g-3 mb-4">
  <?php foreach ($s as $c): ?><div class="col-sm-6 col-xl"><div class="card stat-card p-3"><div class="d-flex align-items-center gap-3">
    <div class="stat-ico" style="background:<?= $c[3] ?>"><i class="bi <?= $c[2] ?>"></i></div><div><div class="text-muted small"><?= $c[0] ?></div><div class="fw-bold fs-5"><?= $c[1] ?></div></div></div></div></div><?php endforeach; ?>
</div>
<div class="row g-3 mb-4">
  <div class="col-lg-8"><div class="card card-soft p-3 h-100"><h6 class="fw-bold">Grafik Transaksi Penyewaan (6 Bulan Terakhir)</h6>
    <div class="bar-chart"><?php foreach ($bulan as $b => $n): ?><div class="bar-col"><small class="fw-semibold"><?= $n ?></small>
      <div class="bar" style="height:<?= round($n / $maks * 150) ?>px"></div><small class="text-muted mt-1"><?= $nm[substr($b, 5)] ?></small></div><?php endforeach; ?></div></div></div>
  <div class="col-lg-4"><div class="card card-soft p-3 h-100"><h6 class="fw-bold">Perlu Tindakan</h6>
    <?php foreach ($perlu as $p): ?><a href="<?= url($p[2]) ?>" class="d-flex justify-content-between align-items-center text-decoration-none border-bottom py-2">
      <span class="text-dark small"><?= $p[0] ?></span><span class="badge <?= $p[1] ? 'text-bg-warning' : 'text-bg-light border' ?> fs-6"><?= $p[1] ?></span></a><?php endforeach; ?></div></div>
</div>
<div class="card card-soft p-3"><div class="d-flex justify-content-between mb-2"><h6 class="fw-bold mb-0">Transaksi Terbaru</h6><a class="small" href="<?= url('admin/transaksi/index.php') ?>">Lihat semua</a></div>
  <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Kode</th><th>Penyewa</th><th>Tanggal Sewa</th><th>Total</th><th>Status</th><th>Bayar</th></tr></thead><tbody>
  <?php foreach ($terbaru as $t): ?><tr><td><a href="<?= url('admin/transaksi/detail.php?id=' . $t['id_transaksi']) ?>"><?= e($t['kode_transaksi']) ?></a></td><td><?= e($t['nama_penyewa']) ?></td>
    <td class="small"><?= tgl($t['tgl_ambil']) ?> – <?= tgl($t['tgl_kembali']) ?></td><td><?= rupiah($t['total_biaya']) ?></td><td><?= badge($t['status']) ?></td><td><?= badge($t['status_bayar']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
