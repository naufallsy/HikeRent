<?php
require_once __DIR__ . '/functions.php';
$u = user(); $cartCount = 0;
if ($u && $u['role'] === 'pelanggan') $cartCount = (int)db_val("SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE id_user=?", [$u['id']]);
$nav = $nav ?? '';
function nav_active($k) { global $nav; return $nav === $k ? 'active' : ''; }
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Beranda') ?> | HikeRent – Penyewaan Alat Hiking</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-hike sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 text-white fw-bold" href="<?= url('index.php') ?>">
      <span class="brand-logo"><i class="bi bi-tree-fill"></i></span> HikeRent</a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><i class="bi bi-list text-white fs-2"></i></button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link <?= nav_active('home') ?>" href="<?= url('index.php') ?>">Beranda</a></li>
        <li class="nav-item"><a class="nav-link <?= nav_active('katalog') ?>" href="<?= url('pelanggan/katalog.php') ?>">Katalog Alat</a></li>
        <li class="nav-item"><a class="nav-link <?= nav_active('tentang') ?>" href="<?= url('tentang.php') ?>">Tentang Kami</a></li>
        <li class="nav-item"><a class="nav-link <?= nav_active('kontak') ?>" href="<?= url('kontak.php') ?>">Kontak</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2">
      <?php if ($u && $u['role'] === 'pelanggan'): ?>
        <a class="btn btn-sm btn-outline-light position-relative" href="<?= url('pelanggan/keranjang.php') ?>"><i class="bi bi-cart3"></i> Keranjang
          <?php if ($cartCount): ?><span class="badge rounded-pill bg-warning text-dark position-absolute top-0 start-100 translate-middle"><?= $cartCount ?></span><?php endif; ?></a>
        <div class="dropdown">
          <button class="btn btn-sm btn-orange dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> <?= e(explode(' ', $u['nama'])[0]) ?></button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= url('pelanggan/riwayat.php') ?>"><i class="bi bi-clock-history"></i> Riwayat Penyewaan</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
          </ul></div>
      <?php elseif ($u): ?>
        <a class="btn btn-sm btn-orange" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
        <a class="btn btn-sm btn-outline-light" href="<?= url('auth/logout.php') ?>">Keluar</a>
      <?php else: ?>
        <a class="btn btn-sm btn-outline-light" href="<?= url('auth/login.php') ?>">Masuk</a>
        <a class="btn btn-sm btn-orange" href="<?= url('auth/register.php') ?>">Daftar</a>
      <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<div class="toast-wrap"><?php show_flash(); ?></div>
<main>
