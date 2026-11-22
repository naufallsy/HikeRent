<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
date_default_timezone_set('Asia/Jakarta');

// ---- Konstanta aplikasi ----
$_root = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$_app  = str_replace('\\', '/', realpath(__DIR__ . '/..'));
define('BASE_URL', strpos($_app, $_root) === 0 ? substr($_app, strlen($_root)) : '/hikerent');
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('DENDA_PERSEN', 50);                       // denda/hari = 50% dari total sewa per hari
define('STATUS_AKTIF', "'menunggu','dikonfirmasi','diambil'");

// ---- Database helper (semua pakai prepared statement) ----
function q($sql, $p = []) {
    global $conn;
    $st = $conn->prepare($sql);
    if ($p) {
        $t = '';
        foreach ($p as $v) $t .= is_int($v) ? 'i' : (is_float($v) ? 'd' : 's');
        $st->bind_param($t, ...$p);
    }
    $st->execute();
    return $st;
}
function db_all($sql, $p = []) { return q($sql, $p)->get_result()->fetch_all(MYSQLI_ASSOC); }
function db_row($sql, $p = []) { $r = db_all($sql, $p); return $r[0] ?? null; }
function db_val($sql, $p = []) { $r = q($sql, $p)->get_result()->fetch_row(); return $r ? $r[0] : null; }
function db_run($sql, $p = []) { $st = q($sql, $p); return $st->insert_id ?: $st->affected_rows; }

// ---- Umum ----
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }
function tgl($d) {
    if (!$d) return '-';
    $b = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($d);
    return date('d', $t) . ' ' . $b[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function url($p = '') { return BASE_URL . '/' . ltrim($p, '/'); }
function redirect($p) {
    if (preg_match('#^https?://#', $p) || (isset($p[0]) && $p[0] === '/')) header('Location: ' . $p);
    else header('Location: ' . url($p));
    exit;
}
function safe_back($p, $default) {
    return (is_string($p) && preg_match('#^/[^/\\\\]#', $p) && strpos($p, BASE_URL) === 0) ? $p : $default;
}
function flash($type, $msg) { $_SESSION['flash'] = ['t' => $type, 'm' => $msg]; }
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['t']) . ' alert-dismissible fade show shadow toast-alert" role="alert">'
           . e($f['m']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}
function badge($s) {
    $map = ['menunggu' => 'warning', 'dikonfirmasi' => 'info', 'diambil' => 'primary', 'selesai' => 'success', 'ditolak' => 'danger',
            'dibatalkan' => 'secondary', 'belum_bayar' => 'secondary', 'menunggu_konfirmasi' => 'warning', 'berhasil' => 'success',
            'tersedia' => 'success', 'tidak_tersedia' => 'danger', 'baik' => 'success', 'rusak_ringan' => 'warning',
            'rusak_berat' => 'danger', 'hilang' => 'dark'];
    return '<span class="badge text-bg-' . ($map[$s] ?? 'secondary') . '">' . e(ucwords(str_replace('_', ' ', $s))) . '</span>';
}
function status_kembali($status, $ret) {
    if ($ret) return '<span class="badge text-bg-success">Sudah dikembalikan</span>';
    if ($status === 'diambil') return '<span class="badge text-bg-warning">Belum dikembalikan</span>';
    if (in_array($status, ['ditolak', 'dibatalkan'])) return '<span class="text-muted">-</span>';
    return '<span class="badge text-bg-secondary">Belum diambil</span>';
}
function valid_date($d) { $x = DateTime::createFromFormat('Y-m-d', (string)$d); return $x && $x->format('Y-m-d') === $d; }
function hitung_durasi($a, $b) { return max(1, (int)(new DateTime($a))->diff(new DateTime($b))->days); }

// ---- Auth & hak akses ----
function is_login() { return !empty($_SESSION['user']); }
function user() { return $_SESSION['user'] ?? null; }
function require_login() {
    if (!is_login()) { flash('warning', 'Silakan login terlebih dahulu.'); redirect('auth/login.php?next=' . urlencode($_SERVER['REQUEST_URI'])); }
}
function require_admin() {
    require_login();
    if (user()['role'] !== 'admin') { flash('danger', 'Akses ditolak. Halaman khusus admin.'); redirect('index.php'); }
}
function require_pelanggan() {
    require_login();
    if (user()['role'] !== 'pelanggan') redirect('admin/dashboard.php');
}

// ---- CSRF ----
function csrf_token() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); die('Token keamanan tidak valid. Silakan kembali dan muat ulang halaman.'); }
}

// ---- Upload gambar (return [namaFile, error]) ----
function upload_gambar($f, $dir, $prefix) {
    if (empty($f['name']) || $f['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Upload gagal (kode ' . $f['error'] . ').'];
    if ($f['size'] > 2 * 1024 * 1024) return [null, 'Ukuran file maksimal 2 MB.'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) return [null, 'Format file harus JPG, PNG, atau WEBP.'];
    $path = ROOT_PATH . '/' . $dir;
    if (!is_dir($path)) mkdir($path, 0775, true);
    $name = $prefix . '-' . time() . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $path . '/' . $name)) return [null, 'Gagal menyimpan file ke server.'];
    return [$name, null];
}
function foto_url($f) { return url('assets/images/alat/' . rawurlencode($f ?: 'default.svg')); }
function bukti_url($f) { return url('uploads/bukti/' . rawurlencode($f)); }

// ---- Stok & jadwal ----
// Jumlah unit yang sudah terpakai/terpesan pada rentang tanggal (transaksi menunggu/dikonfirmasi/diambil yang bertabrakan)
function stok_terpesan($id, $from, $to, $exclude = 0) {
    return (int)db_val("SELECT COALESCE(SUM(d.jumlah),0) FROM detail_transaksi d JOIN transaksi t ON t.id_transaksi=d.id_transaksi
        WHERE d.id_alat=? AND t.status IN (" . STATUS_AKTIF . ") AND t.id_transaksi<>? AND t.tgl_ambil<=? AND t.tgl_kembali>=?", [$id, $exclude, $to, $from]);
}
// Stok yang masih bisa disewa pada rentang tanggal (default: hari ini)
function stok_tersedia($id, $from = null, $to = null, $exclude = 0) {
    $a = db_row("SELECT stok, status FROM alat WHERE id_alat=?", [$id]);
    if (!$a || $a['status'] !== 'tersedia') return 0;
    $from = $from ?: date('Y-m-d'); $to = $to ?: $from;
    return max(0, (int)$a['stok'] - stok_terpesan($id, $from, $to, $exclude));
}
// Pendapatan = pembayaran berhasil (transaksi tidak batal/ditolak) + denda
function total_pendapatan($where = '1=1', $p = []) {
    $a = (int)db_val("SELECT COALESCE(SUM(p.jumlah),0) FROM pembayaran p JOIN transaksi t ON t.id_transaksi=p.id_transaksi
         WHERE p.status='berhasil' AND t.status NOT IN ('ditolak','dibatalkan') AND $where", $p);
    return $a;
}

// ---- Komponen kartu produk ----
function product_card($a) {
    $sisa = stok_tersedia($a['id_alat']);
    ob_start(); ?>
    <div class="col-sm-6 col-lg-4 col-xl-3"><div class="card product-card h-100">
      <div class="product-img"><img src="<?= foto_url($a['foto']) ?>" alt="<?= e($a['nama_alat']) ?>" loading="lazy">
        <span class="badge cat-badge"><?= e($a['nama_kategori']) ?></span></div>
      <div class="card-body d-flex flex-column">
        <h6 class="fw-semibold mb-1"><?= e($a['nama_alat']) ?></h6>
        <p class="small text-muted mb-2 clamp-2"><?= e($a['deskripsi']) ?></p>
        <div class="d-flex justify-content-between align-items-end mb-3">
          <div class="price"><?= rupiah($a['harga_sewa']) ?><small>/hari</small></div>
          <?= $sisa > 0 ? '<span class="badge text-bg-success">Tersedia: ' . $sisa . '</span>' : '<span class="badge text-bg-danger">Tidak tersedia</span>' ?>
        </div>
        <div class="mt-auto d-flex gap-2">
          <a class="btn btn-outline-forest btn-sm flex-fill" href="<?= url('pelanggan/detail.php?id=' . $a['id_alat']) ?>">Detail</a>
          <form method="post" action="<?= url('pelanggan/keranjang.php') ?>" class="flex-fill">
            <?= csrf_field() ?><input type="hidden" name="action" value="tambah"><input type="hidden" name="id_alat" value="<?= $a['id_alat'] ?>">
            <input type="hidden" name="jumlah" value="1"><input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
            <button class="btn btn-orange btn-sm w-100" <?= $sisa > 0 ? '' : 'disabled' ?>><i class="bi bi-bag-plus"></i> Sewa</button>
          </form>
        </div>
      </div></div></div>
    <?php return ob_get_clean();
}
