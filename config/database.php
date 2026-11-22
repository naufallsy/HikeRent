<?php
// Koneksi database (dipisah supaya mudah diubah)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';          // XAMPP/Laragon default: kosong
$DB_NAME = 'db_hikerent';
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal. Pastikan MySQL aktif dan database db_hikerent sudah di-import. (' . htmlspecialchars($e->getMessage()) . ')');
}
