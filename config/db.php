<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$user = 'root'; // Sesuaikan username database Anda
$pass = '';     // Sesuaikan password database Anda
$db   = 'tavernex_db';

mysqli_report(MYSQLI_REPORT_OFF);
try {
    $conn = new mysqli($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    die("<div style='padding:20px; font-family:sans-serif; background:#fee2e2; color:#991b1b; border:1px solid #f87171; border-radius:8px;'>
        <strong>KONEKSI DATABASE GAGAL! (HTTP 500 dicegah)</strong><br><br>
        Pesan Error MySQL: " . $e->getMessage() . "<br><br>
        <strong>Solusi:</strong> Buka file <code>config/db.php</code> dan pastikan variabel <code>\$user</code>, <code>\$pass</code>, dan <code>\$db</code> sudah diubah sesuai dengan detail database MySQL Anda di CPanel (misal: <code>althof_db</code>).
    </div>");
}
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

/**
 * Polyfill untuk $stmt->get_result() yang kompatibel dengan mysqli
 * tanpa mysqlnd driver (untuk CPanel hosting yang tidak support nd_mysqli).
 * 
 * Fungsi ini menggunakan bind_result() + fetch() sebagai pengganti get_result().
 * Mengembalikan array asosiatif dari hasil prepared statement.
 *
 * @param mysqli_stmt $stmt - Prepared statement yang sudah di-execute()
 * @return array - Array of associative arrays (rows)
 */
function stmt_get_all($stmt) {
    $meta = $stmt->result_metadata();
    if (!$meta) return [];
    
    $fields = [];
    $row = [];
    while ($field = $meta->fetch_field()) {
        $fields[] = $field->name;
        $row[$field->name] = null;
    }
    
    // Buat references untuk bind_result
    $refs = [];
    foreach ($fields as $f) {
        $refs[] = &$row[$f];
    }
    
    call_user_func_array([$stmt, 'bind_result'], $refs);
    
    $results = [];
    while ($stmt->fetch()) {
        $copy = [];
        foreach ($fields as $f) {
            $copy[$f] = $row[$f];
        }
        $results[] = $copy;
    }
    
    $meta->free();
    return $results;
}

/**
 * Helper: Ambil satu baris pertama dari prepared statement.
 * Pengganti $stmt->get_result()->fetch_assoc()
 *
 * @param mysqli_stmt $stmt - Prepared statement yang sudah di-execute()
 * @return array|null - Associative array atau null jika tidak ada hasil
 */
function stmt_fetch_assoc($stmt) {
    $rows = stmt_get_all($stmt);
    return $rows[0] ?? null;
}

/**
 * Helper: Hitung jumlah baris dari prepared statement.
 * Pengganti $stmt->get_result()->num_rows
 *
 * @param mysqli_stmt $stmt - Prepared statement yang sudah di-execute()
 * @return int - Jumlah baris hasil
 */
function stmt_num_rows($stmt) {
    $stmt->store_result();
    return $stmt->num_rows;
}

// Fungsi helper UI
function formatRupiah($angka){
    return "Rp " . number_format($angka,0,',','.');
}

function getRoleColor($role) {
    if($role === 'admin') return 'text-red-400';
    if($role === 'seller') return 'text-amber-400';
    if($role === 'system') return 'text-slate-400';
    return 'text-blue-400';
}

function calculateAdminFee($price) {
    if ($price <= 0) return 0;
    $base_fee = 2000;
    $increments = floor(($price - 1) / 50000);
    return $base_fee + ($increments * 500);
}

?>