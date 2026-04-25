<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';

// Process accept order
if(isset($_POST['accept_order'])) {
    $trx_id = $conn->real_escape_string($_POST['trx_id']);
    
    // Update status to processing
    $conn->query("UPDATE transactions SET status='processing' WHERE id='$trx_id'");
    
    // Auto chat from seller
    $msg = "Halo Pembeli! Saya sedang memproses data akun/item. Silakan tunggu sebentar.";
    $ins = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, 'seller', ?, ?)");
    $ins->bind_param("sss", $trx_id, $_SESSION['username'], $msg);
    $ins->execute();
    
    $msg_success = "Pesanan berhasil diterima dan masuk ke tahap proses Escrow.";
}

// Proses cancel order
if(isset($_POST['cancel_order'])) {
    $trx_id = $conn->real_escape_string($_POST['trx_id']);
    $conn->query("UPDATE transactions SET status='cancelled' WHERE id='$trx_id'");
}

// Fetch orders
$q_str = "SELECT t.*, p.title, p.game, p.color_theme, p.image_url, u.username as buyer_name 
        FROM transactions t 
        JOIN products p ON t.product_id = p.id 
        JOIN users u ON t.buyer_id = u.id 
        WHERE p.seller_id = '$seller_id'";

if($status_filter !== 'all') {
    $q_str .= " AND t.status = '$status_filter'";
}
$q_str .= " ORDER BY t.created_at DESC";

$orders_q = $conn->query($q_str);
$orders = [];
while($row = $orders_q->fetch_assoc()) {
    $orders[] = $row;
}

$seller_q = $conn->query("SELECT * FROM users WHERE id='$seller_id'");
$seller = $seller_q->fetch_assoc();

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
                <h3 class="font-bold text-white leading-tight truncate"><?= $_SESSION['username'] ?></h3>
                <a href="store_profile.php?id=<?= $seller_id ?>" class="text-xs text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded border border-blue-400/30 mt-1 inline-block hover:bg-blue-400/20 transition">Lihat Etalase</a>
            </div>
        </div>
        <div class="p-4 space-y-1 text-sm font-semibold">
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-4 px-2">Tokoku</div>
            <a href="seller_dashboard.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-house"></i> Beranda</a>
            <a href="store_settings.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-gear"></i> Pengaturan Toko</a>
            
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-6 px-2">Pesanan</div>
            <a href="seller_orders.php" class="flex items-center gap-3 px-3 py-2 text-emerald-400 bg-emerald-500/10 rounded-lg transition"><i class="ph-fill ph-receipt"></i> Riwayat Pesanan</a>
            
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-6 px-2">Dagangan</div>
            <a href="add_product.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-plus-circle"></i> Buat Dagangan</a>
            <a href="seller_products.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-list-dashes"></i> Daganganku</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow p-6 md:p-8 overflow-y-auto">
        <h1 class="text-2xl font-bold text-white mb-6 border-b border-slate-700 pb-2">Pesanan Masuk</h1>
        
        <?php if(isset($msg_success)): ?>
            <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl border border-emerald-500/30 font-semibold mb-6 flex items-center gap-3">
                <i class="ph-fill ph-check-circle text-2xl"></i> <?= $msg_success ?>
            </div>
        <?php endif; ?>

        <!-- Nav Filter -->
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            <a href="?status=all" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg border <?= $status_filter == 'all' ? 'bg-slate-700 text-white border-slate-600' : 'bg-transparent text-slate-400 border-transparent hover:bg-slate-800' ?>">Semua</a>
            <a href="?status=pending" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg border <?= $status_filter == 'pending' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-transparent text-slate-400 border-transparent hover:bg-slate-800' ?>">Perlu Diproses</a>
            <a href="?status=processing" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg border <?= $status_filter == 'processing' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-transparent text-slate-400 border-transparent hover:bg-slate-800' ?>">Sedang Berjalan (Escrow)</a>
            <a href="?status=completed" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg border <?= $status_filter == 'completed' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-transparent text-slate-400 border-transparent hover:bg-slate-800' ?>">Selesai</a>
        </div>

        <!-- Order List -->
        <div class="space-y-4">
            <?php if(count($orders) == 0): ?>
                <div class="bg-slate-800 border border-slate-700 p-10 rounded-2xl text-center">
                    <i class="ph ph-receipt text-6xl text-slate-600 mb-4 inline-block"></i>
                    <h3 class="text-white font-bold text-lg">Belum ada pesanan</h3>
                    <p class="text-slate-500 mt-2">Daftar transaksi untuk status ini kosong.</p>
                </div>
            <?php endif; ?>

            <?php foreach($orders as $o): ?>
                <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 hover:border-slate-600 transition">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-700 pb-3">
                        <div class="flex items-center gap-3 text-slate-300 font-semibold text-sm">
                            <i class="ph-fill ph-user-circle"></i> Pembeli: <?= $o['buyer_name'] ?>
                            <span class="text-slate-500 font-normal ml-2"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                        </div>
                        <?php 
                            $status_colors = [
                                'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'cancelling' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20'
                            ];
                            $status_labels = [
                                'pending' => 'Pesanan Baru (Perlu Diproses)',
                                'processing' => 'Sedang Diproses (Masuk Escrow)',
                                'completed' => 'Pesanan Selesai',
                                'cancelling' => 'Sedang Dibatalkan',
                                'cancelled' => 'Dibatalkan'
                            ];
                        ?>
                        <span class="text-xs font-bold px-3 py-1 rounded border <?= $status_colors[$o['status']] ?> uppercase tracking-wider"><?= $status_labels[$o['status']] ?></span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 items-center">
                        <div class="w-16 h-16 rounded-xl flex-shrink-0 bg-slate-700 border border-slate-600 overflow-hidden relative">
                            <?php if($o['image_url']): ?>
                                <img src="../<?= $o['image_url'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full <?= $o['color_theme'] ?>"></div>
                            <?php endif; ?>
                            <div class="absolute inset-0 flex items-center justify-center text-white/50 text-[8px] font-bold text-center leading-tight bg-slate-900/40"><?= $o['game'] ?></div>
                        </div>
                        <div class="flex-grow text-center sm:text-left">
                            <h3 class="text-white font-bold mb-1 line-clamp-1"><?= $o['title'] ?></h3>
                            <p class="text-sm text-slate-400 font-mono text-xs">TRX ID: <?= $o['id'] ?></p>
                            <p class="text-sm text-slate-400 text-xs">Metode: <?= $o['payment_method'] ?></p>
                        </div>
                        <div class="text-right flex flex-col gap-2 min-w-[150px]">
                            <span class="text-slate-400 text-xs uppercase tracking-wider font-bold">Harga Jual</span>
                            <span class="text-emerald-400 font-extrabold text-xl"><?= formatRupiah($o['total_price']) ?></span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-700 flex flex-wrap justify-end gap-3">
                        <?php if($o['status'] == 'pending'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="trx_id" value="<?= $o['id'] ?>">
                                <button type="submit" name="cancel_order" onclick="return confirm('Tolak pesanan ini?')" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-2.5 px-5 rounded-lg text-sm transition">Tolak Masalah Stok</button>
                            </form>
                            <form method="POST" class="inline">
                                <input type="hidden" name="trx_id" value="<?= $o['id'] ?>">
                                <button type="submit" name="accept_order" class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-2.5 px-5 rounded-lg text-sm transition shadow-lg shadow-emerald-500/20">Proses Pesanan</button>
                            </form>
                        <?php elseif($o['status'] == 'processing'): ?>
                            <a href="escrow.php?id=<?= $o['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-lg text-sm transition flex items-center gap-2"><i class="ph-fill ph-chats"></i> Chat Pembeli & Selaesaikan</a>
                        <?php elseif($o['status'] == 'completed'): ?>
                            <span class="text-emerald-500/80 font-bold text-sm px-4 py-2 bg-emerald-500/10 rounded-lg border border-emerald-500/20">Dana cair: <?= formatRupiah($o['total_price']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
