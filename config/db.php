<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$user = 'root'; // Sesuaikan username database Anda
$pass = '';     // Sesuaikan password database Anda
$db   = 'tavernex_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
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