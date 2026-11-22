<?php
require_once __DIR__ . '/../includes/functions.php';
require_pelanggan();
$uid = user()['id'];
$cart = db_all("SELECT k.*, a.nama_alat, a.harga_sewa, a.foto FROM keranjang k JOIN alat a ON a.id_alat=k.id_alat WHERE k.id_user=? ORDER BY k.id_keranjang", [$uid]);
if (!$cart) { flash('warning', 'Keranjang masih kosong.'); redirect('pelanggan/keranjang.php'); }
$me = db_row("SELECT * FROM users WHERE id_user=?", [$uid]);
$in = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$tgl_ambil = $in['tgl_ambil'] ?? date('Y-m-d', strtotime('+1 day'));
$tgl_kembali = $in['tgl_kembali'] ?? date('Y-m-d', strtotime('+2 days'));
$nama = trim($in['nama_penyewa'] ?? $me['nama']); $telp = trim($in['no_telp'] ?? $me['no_telp']); $alamat = trim($in['alamat'] ?? $me['alamat']);
$metode = $in['metode'] ?? '';
$metodeList = ['Transfer BCA', 'Transfer Mandiri', 'QRIS', 'DANA'];
$errors = [];
$today = date('Y-m-d');
if (!valid_date($tgl_ambil)) $tgl_ambil = date('Y-m-d', strtotime('+1 day'));
if (!valid_date($tgl_kembali) || $tgl_kembali < $tgl_ambil) $tgl_kembali = $tgl_ambil;
$perHari = 0; foreach ($cart as $c) $perHari += $c['harga_sewa'] * $c['jumlah'];
$durasi = hitung_durasi($tgl_ambil, $tgl_kembali);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (strlen($nama) < 3) $errors[] = 'Nama penyewa minimal 3 karakter.';
    if (!preg_match('/^[0-9+]{8,15}$/', $telp)) $errors[] = 'Nomor telepon harus 8-15 digit angka.';
    if (strlen($alamat) < 5) $errors[] = 'Alamat minimal 5 karakter.';
    if (!valid_date($_POST['tgl_ambil'] ?? '') || !valid_date($_POST['tgl_kembali'] ?? '')) $errors[] = 'Format tanggal tidak valid.';
    elseif ($_POST['tgl_ambil'] < $today) $errors[] = 'Tanggal pengambilan tidak boleh sebelum hari ini.';
    elseif ($_POST['tgl_kembali'] < $_POST['tgl_ambil']) $errors[] = 'Tanggal pengembalian tidak boleh sebelum tanggal pengambilan.';
    if (!in_array($metode, $metodeList, true)) $errors[] = 'Pilih metode pembayaran.';
    if (!$errors) {
        global $conn;
        try {
            $conn->begin_transaction();
            foreach ($cart as $c) {
                db_row("SELECT id_alat FROM alat WHERE id_alat=? FOR UPDATE", [$c['id_alat']]);   // kunci baris agar tidak bentrok
                $sisa = stok_tersedia($c['id_alat'], $tgl_ambil, $tgl_kembali);
                if ($sisa < $c['jumlah']) $errors[] = 'Stok "' . $c['nama_alat'] . '" pada tanggal tersebut hanya tersedia ' . $sisa . ' unit (diminta ' . $c['jumlah'] . ').';
            }
            if ($errors) { $conn->rollback(); }
            else {
                $total = $perHari * $durasi;
                $kode = 'HR' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                $idt = db_run("INSERT INTO transaksi (kode_transaksi,id_user,nama_penyewa,no_telp,alamat,tgl_ambil,tgl_kembali,durasi,total_biaya) VALUES (?,?,?,?,?,?,?,?,?)",
                    [$kode, $uid, $nama, $telp, $alamat, $tgl_ambil, $tgl_kembali, $durasi, $total]);
                foreach ($cart as $c) db_run("INSERT INTO detail_transaksi (id_transaksi,id_alat,jumlah,harga_satuan,subtotal) VALUES (?,?,?,?,?)",
                    [$idt, $c['id_alat'], (int)$c['jumlah'], (int)$c['harga_sewa'], $c['harga_sewa'] * $c['jumlah'] * $durasi]);
                db_run("INSERT INTO pembayaran (id_transaksi,metode,jumlah) VALUES (?,?,?)", [$idt, $metode, $total]);
                db_run("DELETE FROM keranjang WHERE id_user=?", [$uid]);
                $conn->commit();
                flash('success', 'Checkout berhasil! Kode transaksi ' . $kode . '. Silakan upload bukti pembayaran.');
                redirect('pelanggan/transaksi.php?id=' . $idt);
            }
        } catch (Throwable $ex) { $conn->rollback(); $errors[] = 'Terjadi kesalahan sistem: ' . $ex->getMessage(); }
    }
}
$pageTitle = 'Checkout'; $nav = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-head"><div class="container"><h2 class="fw-bold mb-0"><i class="bi bi-bag-check"></i> Checkout Penyewaan</h2></div></section>
<div class="container py-4">
  <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="row g-4" data-perhari="<?= $perHari ?>"><?= csrf_field() ?>
    <div class="col-lg-7"><div class="card card-soft p-4">
      <h5 class="fw-bold mb-3">Data Penyewa</h5>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nama Penyewa</label><input name="nama_penyewa" class="form-control" value="<?= e($nama) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Nomor Telepon</label><input name="no_telp" class="form-control" value="<?= e($telp) ?>" required></div>
        <div class="col-12"><label class="form-label">Alamat</label><textarea name="alamat" rows="2" class="form-control" required><?= e($alamat) ?></textarea></div>
        <div class="col-md-6"><label class="form-label">Tanggal Pengambilan</label><input type="date" id="tgl_ambil" name="tgl_ambil" min="<?= $today ?>" class="form-control" value="<?= e($tgl_ambil) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Tanggal Pengembalian</label><input type="date" id="tgl_kembali" name="tgl_kembali" min="<?= $today ?>" class="form-control" value="<?= e($tgl_kembali) ?>" required></div>
        <div class="col-12"><label class="form-label">Metode Pembayaran</label><select name="metode" class="form-select" required><option value="">-- Pilih --</option>
          <?php foreach ($metodeList as $m): ?><option <?= $metode === $m ? 'selected' : '' ?>><?= $m ?></option><?php endforeach; ?></select>
          <div class="form-text">Instruksi pembayaran dan upload bukti tersedia setelah checkout.</div></div>
      </div></div></div>
    <div class="col-lg-5"><div class="card card-soft p-4">
      <h5 class="fw-bold mb-3">Barang yang Disewa</h5>
      <?php foreach ($cart as $c): ?><div class="d-flex align-items-center gap-2 mb-2"><img class="cart-img" src="<?= foto_url($c['foto']) ?>" alt="">
        <div class="flex-fill"><div class="fw-semibold small"><?= e($c['nama_alat']) ?></div><small class="text-muted"><?= $c['jumlah'] ?> × <?= rupiah($c['harga_sewa']) ?>/hari</small></div>
        <div class="small fw-semibold"><?= rupiah($c['harga_sewa'] * $c['jumlah']) ?></div></div><?php endforeach; ?>
      <hr><div class="d-flex justify-content-between small"><span>Total per hari</span><span><?= rupiah($perHari) ?></span></div>
      <div class="d-flex justify-content-between small"><span>Durasi sewa</span><span id="durasi"><?= $durasi ?> hari</span></div>
      <div class="d-flex justify-content-between fw-bold fs-5 mt-2"><span>Total Biaya</span><span class="price" id="total"><?= rupiah($perHari * $durasi) ?></span></div>
      <button class="btn btn-orange w-100 mt-3"><i class="bi bi-check2-circle"></i> Konfirmasi Checkout</button>
      <a href="<?= url('pelanggan/keranjang.php') ?>" class="btn btn-link w-100">Kembali ke keranjang</a>
    </div></div>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
