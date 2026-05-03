<?php
require_once 'config/db.php';

echo "<h2>Database Updater</h2>";

// Cek apakah kolom full_name sudah ada
$check = $conn->query("SHOW COLUMNS FROM `verification_requests` LIKE 'full_name'");

if ($check && $check->num_rows > 0) {
    echo "<p style='color:green;'>Database sudah up-to-date! Kolom 'full_name' dan 'nik_ktp' sudah ada di tabel verification_requests.</p>";
} else {
    // Jalankan perintah ALTER TABLE
    // Hapus kolom ktp_image jika ada, dan tambahkan full_name serta nik_ktp
    $alter_sql = "ALTER TABLE `verification_requests` 
                  DROP COLUMN IF EXISTS `ktp_image`,
                  ADD COLUMN `full_name` varchar(150) NOT NULL AFTER `seller_id`,
                  ADD COLUMN `nik_ktp` varchar(50) NOT NULL AFTER `full_name`";
                  
    if ($conn->query($alter_sql)) {
        echo "<p style='color:blue;'>BERHASIL! Struktur tabel 'verification_requests' telah diperbarui.</p>";
    } else {
        echo "<p style='color:red;'>GAGAL memperbarui tabel: " . $conn->error . "</p>";
    }
}

echo "<br><a href='index.php'>Kembali ke Beranda</a>";
?>
