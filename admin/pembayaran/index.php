<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $id = (int)($_POST['id_pembayaran'] ?? 0); $st = $_POST['status'] ?? ''; $cat = trim($_POST['catatan'] ?? '');
    $p = db_row("SELECT * FROM pembayaran WHERE id_pembayaran=?", [$id]);
    if (!$p) flash('danger', 'Data pembayaran tidak ditemukan.');
    elseif (!in_array($st, ['menunggu_konfirmasi', 'berhasil', 'ditolak'])) flash('danger', 'Status tidak valid.');
    elseif (!$p['bukti']) flash('warning', 'Pelanggan belum mengunggah bukti pembayaran.');
    elseif ($st === 'ditolak' && $cat === '') flash('warning', 'Isi catatan alasan penolakan pembayaran.');
    else {
        db_run("UPDATE pembayaran SET status=?, catatan=?, tgl_verifikasi=" . ($st === 'menunggu_konfirmasi' ? 'NULL' : 'NOW()') . " WHERE id_pembayaran=?", [$st, $st === 'ditolak' ? $cat : null, $id]);
        flash('success', 'Status pembayaran diperbarui menjadi ' . str_replace('_', ' ', $st) . '.');
    }
    redirect('admin/pembayaran/index.php' . (!empty($_POST['back_status']) ? '?status=' . urlencode($_POST['back_status']) : ''));
}
$st = $_GET['status'] ?? ''; $q = trim($_GET['q'] ?? '');
$where = ['(t.kode_transaksi LIKE ? OR t.nama_penyewa LIKE ?)']; $par = ["%$q%", "%$q%"];
if (in_array($st, ['belum_bayar', 'menunggu_konfirmasi', 'berhasil', 'ditolak'])) { $where[] = 'p.status=?'; $par[] = $st; }
$rows = db_all("SELECT p.*, t.kode_transaksi, t.nama_penyewa, t.id_transaksi AS tid, t.status AS status_trx FROM pembayaran p JOIN transaksi t ON t.id_transaksi=p.id_transaksi WHERE " . implode(' AND ', $where) . " ORDER BY FIELD(p.status,'menunggu_konfirmasi','belum_bayar','ditolak','berhasil'), p.id_pembayaran DESC", $par);
$pageTitle = 'Manajemen Pembayaran'; $adminNav = 'pembayaran';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="card card-soft p-3">
  <form class="row g-2 mb-3" method="get"><div class="col-md-4"><input name="q" class="form-control" placeholder="Cari kode / nama" value="<?= e($q) ?>"></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Semua status</option>
      <?php foreach (['belum_bayar', 'menunggu_konfirmasi', 'berhasil', 'ditolak'] as $s): ?><option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $s)) ?></option><?php endforeach; ?></select></div>
    <div class="col-auto"><button class="btn btn-forest">Filter</button></div></form>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Transaksi</th><th>Metode</th><th>Jumlah</th><th>Bukti</th><th>Status</th><th style="min-width:300px">Ubah Status</th></tr></thead><tbody>
  <?php foreach ($rows as $p): ?><tr>
    <td><a href="<?= url('admin/transaksi/detail.php?id=' . $p['tid']) ?>" class="fw-semibold"><?= e($p['kode_transaksi']) ?></a><br><small class="text-muted"><?= e($p['nama_penyewa']) ?></small></td>
    <td><?= e($p['metode']) ?></td><td><?= rupiah($p['jumlah']) ?></td>
    <td><?php if ($p['bukti']): ?><a href="<?= bukti_url($p['bukti']) ?>" target="_blank"><img src="<?= bukti_url($p['bukti']) ?>" class="cart-img" alt="Bukti"></a><br><small class="text-muted"><?= tgl($p['tgl_upload']) ?></small><?php else: ?><span class="text-muted small">Belum upload</span><?php endif; ?></td>
    <td><?= badge($p['status']) ?><?= $p['catatan'] ? '<br><small class="text-danger">' . e($p['catatan']) . '</small>' : '' ?></td>
    <td><?php if ($p['bukti']): ?><form method="post" class="d-flex gap-1 flex-wrap"><?= csrf_field() ?><input type="hidden" name="id_pembayaran" value="<?= $p['id_pembayaran'] ?>"><input type="hidden" name="back_status" value="<?= e($st) ?>">
      <select name="status" class="form-select form-select-sm" style="width:auto"><?php foreach (['menunggu_konfirmasi' => 'Menunggu Konfirmasi', 'berhasil' => 'Berhasil', 'ditolak' => 'Ditolak'] as $v => $l): ?><option value="<?= $v ?>" <?= $p['status'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select>
      <input name="catatan" class="form-control form-control-sm" style="width:130px" placeholder="Catatan (jika ditolak)"><button class="btn btn-sm btn-forest">Simpan</button></form>
      <?php else: ?><span class="text-muted small">Menunggu pelanggan upload bukti</span><?php endif; ?></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data pembayaran.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
