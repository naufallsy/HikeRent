<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_login()) redirect('index.php');
$errors = []; $d = ['nama' => '', 'email' => '', 'no_telp' => '', 'alamat' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($d as $k => $_) $d[$k] = trim($_POST[$k] ?? '');
    $pass = $_POST['password'] ?? ''; $conf = $_POST['konfirmasi'] ?? '';
    if (strlen($d['nama']) < 3) $errors[] = 'Nama minimal 3 karakter.';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    elseif (db_val("SELECT COUNT(*) FROM users WHERE email=?", [$d['email']])) $errors[] = 'Email sudah terdaftar.';
    if (!preg_match('/^[0-9+]{8,15}$/', $d['no_telp'])) $errors[] = 'Nomor telepon harus 8-15 digit angka.';
    if (strlen($d['alamat']) < 5) $errors[] = 'Alamat minimal 5 karakter.';
    if (strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($pass !== $conf) $errors[] = 'Konfirmasi password tidak cocok.';
    if (!$errors) {
        db_run("INSERT INTO users (nama,email,password,no_telp,alamat,role) VALUES (?,?,?,?,?, 'pelanggan')",
            [$d['nama'], $d['email'], password_hash($pass, PASSWORD_DEFAULT), $d['no_telp'], $d['alamat']]);
        flash('success', 'Registrasi berhasil! Silakan masuk.');
        redirect('auth/login.php');
    }
}
$pageTitle = 'Daftar'; include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5"><div class="auth-box" style="max-width:560px">
  <div class="card card-soft p-4">
    <h4 class="fw-bold text-center mb-3">Buat Akun Pelanggan</h4>
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" novalidate><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Nama Lengkap</label><input name="nama" class="form-control" value="<?= e($d['nama']) ?>" required></div>
      <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($d['email']) ?>" required></div>
      <div class="col-md-6 mb-3"><label class="form-label">No. Telepon</label><input name="no_telp" class="form-control" value="<?= e($d['no_telp']) ?>" required></div></div>
      <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" rows="2" class="form-control" required><?= e($d['alamat']) ?></textarea></div>
      <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <div class="col-md-6 mb-3"><label class="form-label">Konfirmasi Password</label><input type="password" name="konfirmasi" class="form-control" required></div></div>
      <button class="btn btn-forest w-100">Daftar</button>
    </form>
    <p class="text-center small mt-3 mb-0">Sudah punya akun? <a href="<?= url('auth/login.php') ?>">Masuk</a></p>
  </div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
