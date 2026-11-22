<?php
require_once __DIR__ . '/../includes/functions.php';
require_pelanggan();
$uid = user()['id']; $id = (int)($_GET['id'] ?? 0);
$t = db_row("SELECT * FROM transaksi WHERE id_transaksi=? AND id_user=?", [$id, $uid]);   // hanya milik sendiri
if (!$t) { flash('danger', 'Transaksi tidak ditemukan.'); redirect('pelanggan/riwayat.php'); }
$bayar = db_row("SELECT * FROM pembayaran WHERE id_transaksi=?", [$id]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $act = $_POST['action'] ?? '';
    if ($act === 'upload' && $t['status'] === 'menunggu' && in_array($bayar['status'], ['belum_bayar', 'ditolak'])) {
        [$nm, $err] = upload_gambar($_FILES['bukti'] ?? [], 'uploads/bukti', 'bukti' . $id);
        if ($err) flash('danger', $err);
        elseif (!$nm) flash('danger', 'Pilih file bukti pembayaran terlebih dahulu.');
        else {
            if ($bayar['bukti'] && is_file(ROOT_PATH . '/uploads/bukti/' . $bayar['bukti'])) @unlink(ROOT_PATH . '/uploads/bukti/' . $bayar['bukti']);
            db_run("UPDATE pembayaran SET bukti=?, status='menunggu_konfirmasi', catatan=NULL, tgl_upload=NOW() WHERE id_transaksi=?", [$nm, $id]);
            flash('success', 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.');
        }
    } elseif ($act === 'batal' && $t['status'] === 'menunggu') {
        db_run("UPDATE transaksi SET status='dibatalkan' WHERE id_transaksi=? AND id_user=?", [$id, $uid]);
        flash('success', 'Transaksi berhasil dibatalkan.');
    }
    redirect('pelanggan/transaksi.php?id=' . $id);
}
$items = db_all("SELECT d.*, a.nama_alat, a.foto FROM detail_transaksi d JOIN alat a ON a.id_alat=d.id_alat WHERE d.id_transaksi=?", [$id]);
$ret = db_row("SELECT * FROM pengembalian WHERE id_transaksi=?", [$id]);
$pageTitle = 'Detail Transaksi'; $nav = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div><h2 class="fw-bold mb-0"><?= e($t['kode_transaksi']) ?></h2><small class="opacity-75">Dibuat <?= tgl($t['created_at']) ?></small></div><div class="fs-5"><?= badge($t['status']) ?></div></div></section>
<div class="container py-4"><div class="row g-4">
  <div class="col-lg-7">
    <div class="card card-soft p-4 mb-4"><h5 class="fw-bold mb-3">Rincian Sewa</h5>
      <div class="row small mb-3"><div class="col-sm-6"><b>Penyewa:</b> <?= e($t['nama_penyewa']) ?><br><b>Telepon:</b> <?= e($t['no_telp']) ?><br><b>Alamat:</b> <?= e($t['alamat']) ?></div>
        <div class="col-sm-6"><b>Ambil:</b> <?= tgl($t['tgl_ambil']) ?><br><b>Kembali:</b> <?= tgl($t['tgl_kembali']) ?><br><b>Durasi:</b> <?= $t['durasi'] ?> hari</div></div>
      <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Alat</th><th>Harga/hari</th><th>Jml</th><th class="text-end">Subtotal</th></tr></thead><tbody>
        <?php foreach ($items as $i): ?><tr><td><img src="<?= foto_url($i['foto']) ?>" class="cart-img me-2" style="width:40px;height:40px" alt=""><?= e($i['nama_alat']) ?></td>
          <td><?= rupiah($i['harga_satuan']) ?></td><td><?= $i['jumlah'] ?></td><td class="text-end"><?= rupiah($i['subtotal']) ?></td></tr><?php endforeach; ?>
        </tbody><tfoot><tr><th colspan="3" class="text-end">Total Biaya</th><th class="text-end price"><?= rupiah($t['total_biaya']) ?></th></tr>
        <?php if ($ret && $ret['total_denda'] > 0): ?><tr><th colspan="3" class="text-end">Denda</th><th class="text-end text-danger"><?= rupiah($ret['total_denda']) ?></th></tr><?php endif; ?></tfoot></table></div>
    </div>
    <?php if ($ret): ?><div class="card card-soft p-4"><h6 class="fw-bold">Pengembalian</h6><p class="small mb-0">Dikembalikan: <?= tgl($ret['tgl_dikembalikan']) ?> · Kondisi: <?= badge($ret['kondisi']) ?>
      · Terlambat: <?= $ret['hari_terlambat'] ?> hari · Denda: <?= rupiah($ret['total_denda']) ?><?= $ret['catatan'] ? '<br>Catatan: ' . e($ret['catatan']) : '' ?></p></div><?php endif; ?>
  </div>
  <div class="col-lg-5"><div class="card card-soft p-4">
    <h5 class="fw-bold mb-3">Pembayaran</h5>
    <p class="small mb-1">Metode: <b><?= e($bayar['metode']) ?></b></p><p class="small mb-1">Jumlah: <b><?= rupiah($bayar['jumlah']) ?></b></p>
    <p class="small">Status: <?= badge($bayar['status']) ?> &nbsp; Penyewaan: <?= badge($t['status']) ?> &nbsp; <?= status_kembali($t['status'], $ret) ?></p>
    <?php if ($bayar['catatan']): ?><div class="alert alert-danger py-2 small">Catatan admin: <?= e($bayar['catatan']) ?></div><?php endif; ?>
    <?php if ($bayar['bukti']): ?><a href="<?= bukti_url($bayar['bukti']) ?>" target="_blank"><img src="<?= bukti_url($bayar['bukti']) ?>" class="img-fluid rounded border mb-3" style="max-height:220px" alt="Bukti"></a><?php endif; ?>
    <?php if ($t['status'] === 'menunggu' && in_array($bayar['status'], ['belum_bayar', 'ditolak'])): ?>
      <div class="alert alert-info small">Transfer ke <b>BCA 1234567890 a.n. HikeRent</b> / <b>Mandiri 0987654321</b>, atau scan QRIS di toko. Lalu upload buktinya.</div>
      <form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="upload">
        <input type="file" name="bukti" accept="image/*" class="form-control mb-2" required><button class="btn btn-forest w-100"><i class="bi bi-upload"></i> Upload Bukti Pembayaran</button></form>
    <?php elseif ($bayar['status'] === 'menunggu_konfirmasi'): ?><div class="alert alert-warning small mb-0">Bukti pembayaran sedang diperiksa admin.</div>
    <?php elseif ($bayar['status'] === 'berhasil' && $t['status'] === 'menunggu'): ?><div class="alert alert-success small mb-0">Pembayaran diterima. Menunggu konfirmasi penyewaan oleh admin.</div>
    <?php elseif ($t['status'] === 'dikonfirmasi'): ?><div class="alert alert-success small mb-0">Penyewaan dikonfirmasi. Ambil alat pada <?= tgl($t['tgl_ambil']) ?>.</div><?php endif; ?>
    <?php if ($t['status'] === 'menunggu'): ?><form method="post" class="mt-3" data-confirm="Yakin ingin membatalkan transaksi ini?"><?= csrf_field() ?><input type="hidden" name="action" value="batal">
      <button class="btn btn-outline-danger w-100 btn-sm">Batalkan Transaksi</button></form><?php endif; ?>
    <a href="<?= url('pelanggan/riwayat.php') ?>" class="btn btn-link w-100 mt-2">&larr; Kembali ke riwayat</a>
  </div></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
