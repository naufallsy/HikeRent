<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$edit = null; $errors = [];
if (isset($_GET['edit'])) $edit = db_row("SELECT * FROM kategori WHERE id_kategori=?", [(int)$_GET['edit']]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $act = $_POST['action'] ?? ''; $id = (int)($_POST['id_kategori'] ?? 0);
    $nama = trim($_POST['nama_kategori'] ?? ''); $desk = trim($_POST['deskripsi'] ?? '');
    if ($act === 'hapus') {
        if (db_val("SELECT COUNT(*) FROM alat WHERE id_kategori=?", [$id])) flash('warning', 'Kategori masih dipakai oleh data alat, tidak bisa dihapus.');
        else { db_run("DELETE FROM kategori WHERE id_kategori=?", [$id]); flash('success', 'Kategori berhasil dihapus.'); }
        redirect('admin/kategori/index.php');
    }
    if ($act === 'simpan') {
        if (strlen($nama) < 3) $errors[] = 'Nama kategori minimal 3 karakter.';
        elseif (db_val("SELECT COUNT(*) FROM kategori WHERE nama_kategori=? AND id_kategori<>?", [$nama, $id])) $errors[] = 'Nama kategori sudah ada.';
        if (!$errors) {
            if ($id) { db_run("UPDATE kategori SET nama_kategori=?, deskripsi=? WHERE id_kategori=?", [$nama, $desk, $id]); flash('success', 'Kategori berhasil diperbarui.'); }
            else { db_run("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?,?)", [$nama, $desk]); flash('success', 'Kategori berhasil ditambahkan.'); }
            redirect('admin/kategori/index.php');
        }
        $edit = ['id_kategori' => $id, 'nama_kategori' => $nama, 'deskripsi' => $desk];
    }
}
$rows = db_all("SELECT k.*, COUNT(a.id_alat) AS jml FROM kategori k LEFT JOIN alat a ON a.id_kategori=k.id_kategori GROUP BY k.id_kategori ORDER BY k.nama_kategori");
$pageTitle = 'Manajemen Kategori'; $adminNav = 'kategori';
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="row g-4">
  <div class="col-lg-4"><div class="card card-soft p-3"><h6 class="fw-bold"><?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?></h6>
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="simpan"><input type="hidden" name="id_kategori" value="<?= $edit['id_kategori'] ?? 0 ?>">
      <div class="mb-3"><label class="form-label">Nama Kategori</label><input name="nama_kategori" class="form-control" value="<?= e($edit['nama_kategori'] ?? '') ?>" required></div>
      <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" rows="2" class="form-control"><?= e($edit['deskripsi'] ?? '') ?></textarea></div>
      <button class="btn btn-forest"><i class="bi bi-save"></i> Simpan</button><?php if ($edit): ?> <a href="<?= url('admin/kategori/index.php') ?>" class="btn btn-outline-secondary">Batal</a><?php endif; ?>
    </form></div></div>
  <div class="col-lg-8"><div class="card card-soft p-3"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Kategori</th><th>Deskripsi</th><th>Jml Alat</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($rows as $i => $k): ?><tr><td><?= $i + 1 ?></td><td class="fw-semibold"><?= e($k['nama_kategori']) ?></td><td class="small"><?= e($k['deskripsi']) ?></td><td><?= $k['jml'] ?></td>
      <td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= $k['id_kategori'] ?>"><i class="bi bi-pencil"></i></a>
        <form method="post" class="d-inline" data-confirm="Hapus kategori ini?"><?= csrf_field() ?><input type="hidden" name="action" value="hapus"><input type="hidden" name="id_kategori" value="<?= $k['id_kategori'] ?>">
        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
  </tbody></table></div></div></div>
</div>
<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>
