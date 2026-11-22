<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$t = db_row("SELECT * FROM transaksi WHERE id_transaksi=? AND status='diambil'", [$id]);
if (!$t) { flash('warning', 'Transaksi tidak ditemukan atau tidak berstatus "diambil".'); redirect('admin/pengembalian/index.php'); }
$perHari = (int)db_val("SELECT COALESCE(SUM(harga_satuan*jumlah),0) FROM detail_transaksi WHERE id_transaksi=?", [$id]);
$dendaHari = (int)round($perHari * DENDA_PERSEN / 100);
$items = db_all("SELECT d.jumlah, a.nama_alat FROM detail_transaksi d JOIN alat a ON a.id_alat=d.id_alat WHERE d.id_transaksi=?", [$id]);
$errors = []; $tglKembali = date('Y-m-d'); $kondisi = 'baik'; $dk = 0; $cat = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $tglKembali = $_POST['tgl_dikembalikan'] ?? ''; $kondisi = $_POST['kondisi'] ?? ''; $dk = $_POST['denda_kerusakan'] ?? '0'; $cat = trim($_POST['catatan'] ?? '');
    if (!valid_date($tglKembali)) $errors[] = 'Tanggal pengembalian tidak valid.';
    elseif ($tglKembali < $t['tgl_ambil']) $errors[] = 'Tanggal pengembalian tidak boleh sebelum tanggal pengambilan.';
    if (!in_array($kondisi, ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])) $errors[] = 'Pilih kondisi alat.';
    if (!ctype_digit((string)$dk)) $errors[] = 'Denda kerusakan harus berupa angka.';
    if (!$errors) {
        global $conn;
        $telat = max(0, (int)(new DateTime($t['tgl_kembali']))->diff(new DateTime($tglKembali))->format('%r%a'));
        $dTelat = $telat * $dendaHari; $dk = (int)$dk;
        try {
            $conn->begin_transaction();
            db_run("INSERT INTO pengembalian (id_transaksi,tgl_dikembalikan,kondisi,hari_terlambat,denda_keterlambatan,denda_kerusakan,total_denda,catatan,id_admin) VALUES (?,?,?,?,?,?,?,?,?)",
                [$id, $tglKembali, $kondisi, $telat, $dTelat, $dk, $dTelat + $dk, $cat, user()['id']]);
            db_run("UPDATE transaksi SET status='selesai' WHERE id_transaksi=?", [$id]);
            $conn->commit();
            flash('success', 'Pengembalian dicatat, transaksi selesai.' . ($dTelat + $dk > 0 ? ' Total denda: ' . rupiah($dTelat + $dk) . '.' : ''));
            redirect('admin/pengembalian/index.php');
        } catch (Throwable $ex) { $conn->rollback(); $errors[] = 'Gagal menyimpan: ' . $ex->getMessage(); }
    }
}
$pageTitle = 'Proses Pengembalian'; $adminNav = 'pengembalian';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="row g-4"><div class="col-lg-5"><div class="card card-soft p-4"><h6 class="fw-bold"><?= e($t['kode_transaksi']) ?></h6>
  <p class="small mb-2"><?= e($t['nama_penyewa']) ?> · <?= e($t['no_telp']) ?><br>Sewa: <?= tgl($t['tgl_ambil']) ?> – <?= tgl($t['tgl_kembali']) ?> (<?= $t['durasi'] ?> hari)</p>
  <ul class="small"><?php foreach ($items as $i): ?><li><?= e($i['nama_alat']) ?> × <?= $i['jumlah'] ?></li><?php endforeach; ?></ul>
  <div class="alert alert-light border small mb-0">Denda keterlambatan: <b><?= DENDA_PERSEN ?>%</b> dari total sewa per hari = <b><?= rupiah($dendaHari) ?></b> / hari terlambat.</div></div></div>
  <div class="col-lg-7"><div class="card card-soft p-4">
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" class="row g-3"><?= csrf_field() ?>
      <div class="col-md-6"><label class="form-label">Tanggal Dikembalikan</label><input type="date" id="tgl_dikembalikan" name="tgl_dikembalikan" class="form-control" value="<?= e($tglKembali) ?>"
        data-jatuh-tempo="<?= $t['tgl_kembali'] ?>" data-denda-hari="<?= $dendaHari ?>" required></div>
      <div class="col-md-6"><label class="form-label">Kondisi Alat</label><select name="kondisi" class="form-select">
        <?php foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'] as $v => $l): ?><option value="<?= $v ?>" <?= $kondisi === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><div class="p-2 bg-light rounded small">Keterlambatan: <b id="info_telat">0 hari</b><br>Denda keterlambatan: <b id="info_denda">Rp 0</b></div></div>
      <div class="col-md-6"><label class="form-label">Denda Kerusakan/Kehilangan (Rp)</label><input type="number" min="0" name="denda_kerusakan" class="form-control" value="<?= e($dk) ?>"></div>
      <div class="col-12"><label class="form-label">Catatan Pemeriksaan</label><textarea name="catatan" rows="2" class="form-control"><?= e($cat) ?></textarea></div>
      <div class="col-12 d-flex gap-2"><button class="btn btn-forest"><i class="bi bi-check2-circle"></i> Simpan &amp; Selesaikan Transaksi</button><a href="<?= url('admin/pengembalian/index.php') ?>" class="btn btn-outline-secondary">Batal</a></div>
    </form></div></div></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
