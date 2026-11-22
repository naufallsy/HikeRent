<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_login()) redirect(user()['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php');
$errors = []; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? ''); $pass = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($pass === '') $errors[] = 'Password wajib diisi.';
    if (!$errors) {
        $u = db_row("SELECT * FROM users WHERE email=?", [$email]);
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$u['id_user'], 'nama' => $u['nama'], 'email' => $u['email'], 'role' => $u['role']];
            flash('success', 'Selamat datang, ' . $u['nama'] . '!');
            if ($u['role'] === 'admin') redirect('admin/dashboard.php');
            redirect(safe_back($_POST['next'] ?? '', 'index.php'));
        }
        $errors[] = 'Email atau password salah.';
    }
}
$pageTitle = 'Masuk'; include __DIR__ . '/../includes/header.php';
?>
<div class="container py-5"><div class="auth-box">
  <div class="card card-soft p-4">
    <div class="text-center mb-3"><span class="brand-logo mb-2" style="width:52px;height:52px;font-size:1.6rem"><i class="bi bi-tree-fill"></i></span>
      <h4 class="fw-bold">Masuk ke HikeRent</h4><p class="text-muted small">Sewa alat hiking jadi lebih mudah</p></div>
    <?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($_GET['next'] ?? ($_POST['next'] ?? '')) ?>">
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-forest w-100">Masuk</button>
    </form>
    <p class="text-center small mt-3 mb-0">Belum punya akun? <a href="<?= url('auth/register.php') ?>">Daftar sekarang</a></p>
  </div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
