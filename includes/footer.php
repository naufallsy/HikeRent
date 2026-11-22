</main>
<footer class="footer-hike mt-5 pt-5 pb-3">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5"><h5 class="text-white"><i class="bi bi-tree-fill text-warning"></i> HikeRent</h5>
        <p class="small">Sewa perlengkapan hiking lengkap, bersih, dan terawat. Siap menemani setiap langkah pendakianmu.</p></div>
      <div class="col-6 col-md-3"><h6 class="text-white">Menu</h6><ul class="list-unstyled small">
        <li><a href="<?= url('index.php') ?>">Beranda</a></li><li><a href="<?= url('pelanggan/katalog.php') ?>">Katalog Alat</a></li>
        <li><a href="<?= url('tentang.php') ?>">Tentang Kami</a></li><li><a href="<?= url('kontak.php') ?>">Kontak</a></li></ul></div>
      <div class="col-6 col-md-4"><h6 class="text-white">Kontak</h6><ul class="list-unstyled small">
        <li><i class="bi bi-geo-alt"></i> Jl. Raya Dieng No. 10, Wonosobo</li><li><i class="bi bi-telephone"></i> 0812-3456-7890</li>
        <li><i class="bi bi-envelope"></i> halo@hikerent.com</li></ul></div>
    </div>
    <hr class="border-secondary"><p class="text-center small mb-0">&copy; <?= date('Y') ?> HikeRent – Penyewaan Alat Hiking.</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body></html>
