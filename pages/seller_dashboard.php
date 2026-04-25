<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get seller stats
$pending_query = $conn->query("SELECT COUNT(*) as cnt FROM transactions t JOIN products p ON t.product_id = p.id WHERE p.seller_id = '$seller_id' AND t.status = 'pending'");
$pending_count = $pending_query->fetch_assoc()['cnt'];

$processing_query = $conn->query("SELECT COUNT(*) as cnt FROM transactions t JOIN products p ON t.product_id = p.id WHERE p.seller_id = '$seller_id' AND t.status = 'processing'");
$processing_count = $processing_query->fetch_assoc()['cnt'];

$completed_query = $conn->query("SELECT COUNT(*) as cnt FROM transactions t JOIN products p ON t.product_id = p.id WHERE p.seller_id = '$seller_id' AND t.status = 'completed'");
$completed_count = $completed_query->fetch_assoc()['cnt'];

// Keuangan (completed transactions total price)
$keuangan_query = $conn->query("SELECT SUM(p.price) as total FROM transactions t JOIN products p ON t.product_id = p.id WHERE p.seller_id = '$seller_id' AND t.status = 'completed'");
$saldo = $keuangan_query->fetch_assoc()['total'] ?? 0;

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto w-full flex-grow flex flex-col md:flex-row bg-slate-900 min-h-[calc(100vh-64px)]">
    <!-- Sidebar Itemku Style -->
    <div class="w-full md:w-64 bg-slate-800 border-r border-slate-700 flex-shrink-0">
        <div class="p-6 border-b border-slate-700 flex items-center gap-3">
            <div class="h-12 w-12 rounded-full bg-slate-700 flex items-center justify-center text-emerald-500 font-bold text-xl">
                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
            </div>
            <div>
                <h3 class="font-bold text-white leading-tight"><?= $_SESSION['username'] ?></h3>
                <span class="text-xs text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded-full mt-1 inline-block">Toko Aktif</span>
            </div>
        </div>
        <div class="p-4 space-y-1 text-sm font-semibold">
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-4 px-2">Tokoku</div>
            <a href="seller_dashboard.php" class="flex items-center gap-3 px-3 py-2 text-emerald-400 bg-emerald-500/10 rounded-lg"><i class="ph-fill ph-house"></i> Beranda</a>
            <a href="store_settings.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-gear"></i> Pengaturan Toko</a>
            
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-6 px-2">Pesanan</div>
            <a href="seller_orders.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-receipt"></i> Riwayat Pesanan</a>
            
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-6 px-2">Dagangan</div>
            <a href="add_product.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-plus-circle"></i> Buat Dagangan</a>
            <a href="seller_products.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-list-dashes"></i> Daganganku</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow p-6 md:p-8 overflow-y-auto">
        <h1 class="text-2xl font-bold text-white mb-2">Aktifitas Penting</h1>
        <p class="text-slate-400 text-sm mb-8">Hal-hal yang penting untuk kamu cek terkait tokomu.</p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-sm">
                <span class="text-slate-400 text-sm font-semibold mb-2 block">Perlu Diproses</span>
                <div class="flex items-end gap-2 text-white">
                    <i class="ph-fill ph-clock-countdown text-blue-500 text-3xl"></i>
                    <span class="text-2xl font-bold leading-none"><?= $pending_count ?></span>
                </div>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-sm">
                <span class="text-slate-400 text-sm font-semibold mb-2 block">Menunggu Konfirmasi</span>
                <div class="flex items-end gap-2 text-white">
                    <i class="ph-fill ph-hourglass-high text-amber-500 text-3xl"></i>
                    <span class="text-2xl font-bold leading-none"><?= $processing_count ?></span>
                </div>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-sm">
                <span class="text-slate-400 text-sm font-semibold mb-2 block">Transaksi Selesai</span>
                <div class="flex items-end gap-2 text-white">
                    <i class="ph-fill ph-check-circle text-emerald-500 text-3xl"></i>
                    <span class="text-2xl font-bold leading-none"><?= $completed_count ?></span>
                </div>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-sm">
                <span class="text-slate-400 text-sm font-semibold mb-2 block">Kendala Pesanan</span>
                <div class="flex items-end gap-2 text-white">
                    <i class="ph-fill ph-warning-circle text-red-500 text-3xl"></i>
                    <span class="text-2xl font-bold leading-none">0</span>
                </div>
            </div>
        </div>

        <h2 class="text-xl font-bold text-white mb-4">Keuangan Toko</h2>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 flex flex-col md:flex-row items-center justify-between shadow-sm">
            <div class="flex items-center gap-8 w-full">
                <div class="flex-1">
                    <span class="text-slate-400 text-sm font-semibold mb-2 block">Saldo Toko</span>
                    <div class="flex items-center gap-2 text-white">
                        <i class="ph-fill ph-wallet text-emerald-500 text-2xl"></i>
                        <span class="text-2xl font-bold"><?= formatRupiah($saldo) ?></span>
                    </div>
                </div>
                <div class="w-px h-12 bg-slate-700 mx-4 hidden md:block"></div>
                <div class="flex-1 text-right md:text-left mt-4 md:mt-0">
                    <a href="#" class="text-blue-500 font-bold hover:text-blue-400 text-sm">Lihat Detail Keuangan <i class="ph-bold ph-caret-right"></i></a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
