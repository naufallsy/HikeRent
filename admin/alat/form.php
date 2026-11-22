<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$a = ['id_kategori' => '', 'nama_alat' => '', 'deskripsi' => '', 'harga_sewa' => '', 'stok' => '', 'foto' => null, 'status' => 'tersedia'];
if ($id) { $a = db_row("SELECT * FROM alat WHERE id_alat=?", [$id]); if (!$a) { flash('danger', 'Data alat tidak ditemukan.'); redirect('admin/alat/index.php'); } }
$kats = db_all("SELECT * FROM kategori ORDER BY nama_kategori");
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = ['id_kategori' => (int)($_POST['id_kategori'] ?? 0), 'nama_alat' => trim($_POST['nama_alat'] ?? ''), 'deskripsi' => trim($_POST['deskripsi'] ?? ''),
          'harga_sewa' => $_POST['harga_sewa'] ?? '', 'stok' => $_POST['stok'] ?? '', 'status' => $_POST['status'] ?? 'tersedia'];
    if (!db_val("SELECT COUNT(*) FROM kategori WHERE id_kategori=?", [$d['id_kategori']])) $errors[] = 'Pilih kategori yang valid.';
    if (strlen($d['nama_alat']) < 3) $errors[] = 'Nama alat minimal 3 karakter.';
    if (!ctype_digit((string)$d['harga_sewa']) || (int)$d['harga_sewa'] < 1000) $errors[] = 'Harga sewa harus angka minimal 1000.';
    if (!ctype_digit((string)$d['stok'])) $errors[] = 'Stok harus angka 0 atau lebih.';
    if (!in_array($d['status'], ['tersedia', 'tidak_tersedia'])) $errors[] = 'Status tidak valid.';
    [$foto, $err] = upload_gambar($_FILES['foto'] ?? [], 'assets/images/alat', 'alat'); if ($err) $errors[] = $err;
    $a = array_merge($a, $d);
    if (!$errors) {
        $fotoFinal = $foto ?: $a['foto'];
        if ($id) {
            if ($foto && $a['foto'] && !preg_match('/\.svg$/', $a['foto']) && is_file(ROOT_PATH . '/assets/images/alat/' . $a['foto'])) @unlink(ROOT_PATH . '/assets/images/alat/' . $a['foto']);
            db_run("UPDATE alat SET id_kategori=?, nama_alat=?, deskripsi=?, harga_sewa=?, stok=?, foto=?, status=? WHERE id_alat=?",
                [$d['id_kategori'], $d['nama_alat'], $d['deskripsi'], (int)$d['harga_sewa'], (int)$d['stok'], $fotoFinal, $d['status'], $id]);
            flash('success', 'Data alat berhasil diperbarui.');
        } else {
            db_run("INSERT INTO alat (id_kategori,nama_alat,deskripsi,harga_sewa,stok,foto,status) VALUES (?,?,?,?,?,?,?)",
                [$d['id_kategori'], $d['nama_alat'], $d['deskripsi'], (int)$d['harga_sewa'], (int)$d['stok'], $fotoFinal, $d['status']]);
            flash('success', 'Alat baru berhasil ditambahkan.');
        }
        redirect('admin/alat/index.php');
    }
}
$pageTitle = $id ? 'Edit Alat' : 'Tambah Alat'; $adminNav = 'alat';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="card card-soft p-4" style="max-width:820px">
  <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" enctype="multipart/form-data" class="row g-3"><?= csrf_field() ?>
    <div class="col-md-8"><label class="form-label">Nama Alat</label><input name="nama_alat" class="form-control" value="<?= e($a['nama_alat']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Kategori</label><select name="id_kategori" class="form-select" required><option value="">-- Pilih --</option>
      <?php foreach ($kats as $k): ?><option value="<?= $k['id_kategori'] ?>" <?= $a['id_kategori'] == $k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Harga Sewa / Hari (Rp)</label><input type="number" min="1000" step="500" name="harga_sewa" class="form-control" value="<?= e($a['harga_sewa']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Jumlah Stok</label><input type="number" min="0" name="stok" class="form-control" value="<?= e($a['stok']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Status Ketersediaan</label><select name="status" class="form-select">
      <option value="tersedia" <?= $a['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option><option value="tidak_tersedia" <?= $a['status'] === 'tidak_tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option></select></div>
    <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="3" class="form-control"><?= e($a['deskripsi']) ?></textarea></div>
    <div class="col-md-8"><label class="form-label">Foto Alat (JPG/PNG/WEBP, maks 2 MB)</label><input type="file" name="foto" accept="image/*" data-preview="prev" class="form-control">
      <?php if ($id): ?><div class="form-text">Kosongkan jika tidak ingin mengganti foto.</div><?php endif; ?></div>
    <div class="col-md-4"><img id="prev" src="<?= foto_url($a['foto']) ?>" class="detail-img" alt="Preview"></div>
    <div class="col-12 d-flex gap-2"><button class="btn btn-forest px-4"><i class="bi bi-save"></i> Simpan</button><a href="<?= url('admin/alat/index.php') ?>" class="btn btn-outline-secondary">Batal</a></div>
  </form></div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
