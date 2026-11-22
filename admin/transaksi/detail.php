<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$t = db_row("SELECT t.*, u.email FROM transaksi t JOIN users u ON u.id_user=t.id_user WHERE t.id_transaksi=?", [$id]);
if (!$t) { flash('danger', 'Transaksi tidak ditemukan.'); redirect('admin/transaksi/index.php'); }
$bayar = db_row("SELECT * FROM pembayaran WHERE id_transaksi=?", [$id]);
$items = db_all("SELECT d.*, a.nama_alat FROM detail_transaksi d JOIN alat a ON a.id_alat=d.id_alat WHERE d.id_transaksi=?", [$id]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $act = $_POST['action'] ?? '';
    if ($act === 'konfirmasi' && $t['status'] === 'menunggu') {
        if ($bayar['status'] !== 'berhasil') flash('warning', 'Pembayaran belum berstatus "Berhasil". Verifikasi pembayaran terlebih dahulu.');
        else {
            $gagal = [];
            foreach ($items as $i) { $sisa = stok_tersedia($i['id_alat'], $t['tgl_ambil'], $t['tgl_kembali'], $id); if ($sisa < $i['jumlah']) $gagal[] = $i['nama_alat'] . ' (tersedia ' . $sisa . ')'; }
            if ($gagal) flash('danger', 'Stok tidak mencukupi untuk: ' . implode(', ', $gagal));
            else { db_run("UPDATE transaksi SET status='dikonfirmasi' WHERE id_transaksi=?", [$id]); flash('success', 'Penyewaan berhasil dikonfirmasi. Stok dialokasikan pada tanggal sewa.'); }
        }
    } elseif ($act === 'tolak' && $t['status'] === 'menunggu') {
        db_run("UPDATE transaksi SET status='ditolak' WHERE id_transaksi=?", [$id]); flash('success', 'Transaksi ditolak. Stok dilepas kembali.');
    } elseif ($act === 'diambil' && $t['status'] === 'dikonfirmasi') {
        db_run("UPDATE transaksi SET status='diambil' WHERE id_transaksi=?", [$id]); flash('success', 'Status diubah: alat sudah diambil pelanggan.');
    } elseif ($act === 'batal' && $t['status'] === 'dikonfirmasi') {
        db_run("UPDATE transaksi SET status='dibatalkan' WHERE id_transaksi=?", [$id]); flash('success', 'Transaksi dibatalkan. Stok dilepas kembali.');
    }
    redirect('admin/transaksi/detail.php?id=' . $id);
}
$ret = db_row("SELECT * FROM pengembalian WHERE id_transaksi=?", [$id]);
$pageTitle = 'Detail Transaksi'; $adminNav = 'transaksi';
include __DIR__ . '/../../includes/admin_header.php';
function aksi($act, $label, $cls, $conf) { echo '<form method="post" class="d-inline" data-confirm="' . e($conf) . '">' . csrf_field() . '<input type="hidden" name="action" value="' . $act . '"><button class="btn ' . $cls . '">' . $label . '</button></form> '; }
?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card card-soft p-4 mb-4"><div class="d-flex justify-content-between align-items-start"><div><h5 class="fw-bold mb-0"><?= e($t['kode_transaksi']) ?></h5><small class="text-muted">Dibuat <?= tgl($t['created_at']) ?></small></div><?= badge($t['status']) ?></div><hr>
      <div class="row small"><div class="col-sm-6"><b>Penyewa:</b> <?= e($t['nama_penyewa']) ?><br><b>Email akun:</b> <?= e($t['email']) ?><br><b>Telepon:</b> <?= e($t['no_telp']) ?><br><b>Alamat:</b> <?= e($t['alamat']) ?></div>
        <div class="col-sm-6"><b>Tgl Ambil:</b> <?= tgl($t['tgl_ambil']) ?><br><b>Tgl Kembali:</b> <?= tgl($t['tgl_kembali']) ?><br><b>Durasi:</b> <?= $t['durasi'] ?> hari</div></div>
      <div class="table-responsive mt-3"><table class="table table-sm"><thead><tr><th>Alat</th><th>Harga/hari</th><th>Jml</th><th class="text-end">Subtotal</th></tr></thead><tbody>
        <?php foreach ($items as $i): ?><tr><td><?= e($i['nama_alat']) ?></td><td><?= rupiah($i['harga_satuan']) ?></td><td><?= $i['jumlah'] ?></td><td class="text-end"><?= rupiah($i['subtotal']) ?></td></tr><?php endforeach; ?>
      </tbody><tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= rupiah($t['total_biaya']) ?></th></tr></tfoot></table></div>
    </div>
    <?php if ($ret): ?><div class="card card-soft p-4"><h6 class="fw-bold">Data Pengembalian</h6><p class="small mb-0">Tgl kembali: <?= tgl($ret['tgl_dikembalikan']) ?> · Kondisi: <?= badge($ret['kondisi']) ?> · Terlambat <?= $ret['hari_terlambat'] ?> hari ·
      Denda keterlambatan <?= rupiah($ret['denda_keterlambatan']) ?> · Denda kerusakan <?= rupiah($ret['denda_kerusakan']) ?> · <b>Total denda <?= rupiah($ret['total_denda']) ?></b><?= $ret['catatan'] ? '<br>Catatan: ' . e($ret['catatan']) : '' ?></p></div><?php endif; ?>
  </div>
  <div class="col-lg-4">
    <div class="card card-soft p-4 mb-4"><h6 class="fw-bold">Pembayaran</h6><p class="small mb-1">Metode: <?= e($bayar['metode']) ?></p><p class="small mb-1">Jumlah: <?= rupiah($bayar['jumlah']) ?></p><p class="small">Status: <?= badge($bayar['status']) ?></p>
      <?php if ($bayar['bukti']): ?><a href="<?= bukti_url($bayar['bukti']) ?>" target="_blank"><img src="<?= bukti_url($bayar['bukti']) ?>" class="img-fluid rounded border mb-2" style="max-height:200px" alt="Bukti"></a><?php endif; ?>
      <a href="<?= url('admin/pembayaran/index.php?q=' . urlencode($t['kode_transaksi'])) ?>" class="btn btn-sm btn-outline-forest w-100">Kelola Pembayaran</a></div>
    <div class="card card-soft p-4"><h6 class="fw-bold">Aksi</h6>
      <?php if ($t['status'] === 'menunggu'): aksi('konfirmasi', 'Konfirmasi Penyewaan', 'btn-forest mb-2', 'Konfirmasi penyewaan ini?'); aksi('tolak', 'Tolak', 'btn-outline-danger mb-2', 'Tolak transaksi ini?');
      elseif ($t['status'] === 'dikonfirmasi'): aksi('diambil', 'Tandai Alat Sudah Diambil', 'btn-orange mb-2', 'Tandai alat sudah diambil pelanggan?'); aksi('batal', 'Batalkan', 'btn-outline-danger mb-2', 'Batalkan transaksi yang sudah dikonfirmasi?');
      elseif ($t['status'] === 'diambil'): ?><a href="<?= url('admin/pengembalian/proses.php?id=' . $id) ?>" class="btn btn-forest">Proses Pengembalian</a>
      <?php else: ?><p class="small text-muted mb-0">Tidak ada aksi untuk status ini.</p><?php endif; ?>
      <a href="<?= url('admin/transaksi/index.php') ?>" class="btn btn-link px-0 mt-2">&larr; Kembali</a></div>
  </div>
</div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
