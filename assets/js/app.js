document.addEventListener('DOMContentLoaded', function () {
  // Notifikasi hilang otomatis
  document.querySelectorAll('.toast-alert').forEach(function (a) {
    setTimeout(function () { bootstrap.Alert.getOrCreateInstance(a).close(); }, 5000);
  });
  // Konfirmasi sebelum aksi penting
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) { if (!confirm(f.dataset.confirm)) ev.preventDefault(); });
  });
  // Auto-submit (ubah jumlah keranjang)
  document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });
  // Preview gambar sebelum upload
  document.querySelectorAll('input[data-preview]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var img = document.getElementById(inp.dataset.preview);
      if (inp.files[0] && img) img.src = URL.createObjectURL(inp.files[0]);
    });
  });
  // Hitung durasi & total biaya otomatis
  var box = document.querySelector('[data-perhari]');
  if (box) {
    var per = parseInt(box.dataset.perhari, 10), a = document.getElementById('tgl_ambil'), b = document.getElementById('tgl_kembali'),
        d = document.getElementById('durasi'), t = document.getElementById('total');
    var hit = function () {
      if (!a.value || !b.value) return;
      var dur = Math.max(1, Math.round((new Date(b.value) - new Date(a.value)) / 86400000));
      b.min = a.value; d.textContent = dur + ' hari'; t.textContent = 'Rp ' + (per * dur).toLocaleString('id-ID');
    };
    a.addEventListener('change', function () { if (b.value < a.value) b.value = a.value; hit(); });
    b.addEventListener('change', hit); hit();
  }
  // Hitung denda keterlambatan (halaman proses pengembalian)
  var rt = document.getElementById('tgl_dikembalikan');
  if (rt && rt.dataset.jatuhTempo) {
    var upd = function () {
      var late = Math.max(0, Math.round((new Date(rt.value) - new Date(rt.dataset.jatuhTempo)) / 86400000));
      document.getElementById('info_telat').textContent = late + ' hari';
      document.getElementById('info_denda').textContent = 'Rp ' + (late * parseInt(rt.dataset.dendaHari, 10)).toLocaleString('id-ID');
    };
    rt.addEventListener('change', upd); upd();
  }
});
