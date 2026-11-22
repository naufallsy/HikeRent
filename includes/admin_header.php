<?php
require_once __DIR__ . '/functions.php';
require_admin();
$adminNav = $adminNav ?? '';
$pending = (int)db_val("SELECT COUNT(*) FROM pembayaran WHERE status='menunggu_konfirmasi'");
$menu = [
  ['dashboard', 'Dashboard', 'bi-speedometer2', 'admin/dashboard.php'],
  ['alat', 'Data Alat', 'bi-backpack4', 'admin/alat/index.php'],
  ['kategori', 'Kategori', 'bi-tags', 'admin/kategori/index.php'],
  ['pelanggan', 'Pelanggan', 'bi-people', 'admin/pelanggan/index.php'],
  ['transaksi', 'Transaksi', 'bi-receipt', 'admin/transaksi/index.php'],
  ['pembayaran', 'Pembayaran', 'bi-credit-card', 'admin/pembayaran/index.php'],
  ['pengembalian', 'Pengembalian', 'bi-arrow-return-left', 'admin/pengembalian/index.php'],
  ['laporan', 'Laporan', 'bi-printer', 'admin/laporan/index.php'],
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Admin') ?> | Admin HikeRent</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="admin-wrap">
  <div class="offcanvas-lg offcanvas-start admin-side p-3" tabindex="-1" id="sidebar">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <a class="d-flex align-items-center gap-2 text-white fw-bold text-decoration-none fs-5" href="<?= url('admin/dashboard.php') ?>">
        <span class="brand-logo"><i class="bi bi-tree-fill"></i></span> HikeRent</a>
      <button class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
    </div>
    <small class="text-uppercase opacity-50">Menu Admin</small>
    <ul class="nav flex-column mt-2">
      <?php foreach ($menu as $m): ?>
        <li><a class="nav-link <?= $adminNav === $m[0] ? 'active' : '' ?>" href="<?= url($m[3]) ?>"><i class="bi <?= $m[2] ?> me-2"></i><?= $m[1] ?>
          <?php if ($m[0] === 'pembayaran' && $pending): ?><span class="badge bg-warning text-dark ms-1"><?= $pending ?></span><?php endif; ?></a></li>
      <?php endforeach; ?>
      <li class="mt-3"><a class="nav-link" href="<?= url('index.php') ?>"><i class="bi bi-globe me-2"></i>Lihat Website</a></li>
      <li><a class="nav-link text-warning" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
    </ul>
  </div>
  <div class="admin-main">
    <div class="topbar d-flex align-items-center justify-content-between mb-4">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-forest d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar"><i class="bi bi-list"></i></button>
        <h4 class="mb-0 fw-bold text-dark"><?= e($pageTitle ?? '') ?></h4>
      </div>
      <span class="text-muted small d-none d-sm-block"><i class="bi bi-person-circle"></i> <?= e(user()['nama']) ?> (Admin)</span>
    </div>
    <div class="toast-wrap"><?php show_flash(); ?></div>
