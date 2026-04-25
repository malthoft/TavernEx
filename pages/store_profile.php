<?php 
require_once '../config/db.php';

$seller_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT u.* FROM users u WHERE u.id = ? AND u.role='seller'");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();

if(!$seller) {
    header("Location: ../index.php");
    exit;
}

// Fetch stats
$rating_q = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE seller_id = '$seller_id'");
$store_stats = $rating_q->fetch_assoc();
$avg_rating = number_format($store_stats['avg_rating'] ?? 0, 1);
$review_count = $store_stats['review_count'];

$orders_q = $conn->query("SELECT COUNT(*) as completed_orders FROM transactions t JOIN products p ON t.product_id=p.id WHERE p.seller_id='$seller_id' AND t.status='completed'");
$total_orders = $orders_q->fetch_assoc()['completed_orders'];

$success_rate = $total_orders > 0 ? "100%" : "0%"; // simple simulation

// Fetch seller products
$prods_q = $conn->query("SELECT * FROM products WHERE seller_id='$seller_id' AND is_active=1 ORDER BY id DESC LIMIT 20");

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow">
    <!-- Store Header -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-xl mb-8 flex flex-col md:flex-row items-center gap-8">
        <div class="h-32 w-32 rounded-full bg-slate-700 flex items-center justify-center text-5xl font-bold text-emerald-500 border-4 border-slate-900 shadow-xl relative">
            <?= strtoupper(substr($seller['username'], 0, 1)) ?>
            <?php if($seller['is_verified']): ?>
                <div class="absolute bottom-0 right-0 bg-slate-900 rounded-full p-1">
                    <i class="ph-fill ph-seal-check text-emerald-500 text-2xl"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-grow text-center md:text-left">
            <h1 class="text-3xl font-bold text-white mb-2"><?= htmlspecialchars($seller['username']) ?></h1>
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-4">
                <?php if($seller['store_status'] == 'closed'): ?>
                    <span class="bg-red-500/20 text-red-400 font-bold px-3 py-1 rounded-full text-xs border border-red-500/30">Toko Tutup</span>
                <?php else: ?>
                    <span class="bg-emerald-500/20 text-emerald-400 font-bold px-3 py-1 rounded-full text-xs border border-emerald-500/30">Toko Buka</span>
                <?php endif; ?>
                <span class="text-slate-400 text-sm">Beroperasi: <?= substr($seller['store_open_time'], 0, 5) ?> - <?= substr($seller['store_close_time'], 0, 5) ?></span>
            </div>
            
            <div class="flex gap-6 justify-center md:justify-start">
                <div class="text-center md:text-left">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-1">Pesanan Selesai</p>
                    <p class="text-xl font-bold text-white"><?= $total_orders ?></p>
                </div>
                <div class="w-px h-10 bg-slate-700"></div>
                <div class="text-center md:text-left">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-1">Keberhasilan Toko</p>
                    <p class="text-xl font-bold text-emerald-400"><?= $success_rate ?></p>
                </div>
                <div class="w-px h-10 bg-slate-700"></div>
                <div class="text-center md:text-left">
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-1">Rating</p>
                    <p class="text-xl font-bold text-amber-500 flex items-center gap-1"><i class="ph-fill ph-star"></i> <?= $avg_rating ?> <span class="text-xs text-slate-500">(<?= $review_count ?>)</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Products -->
    <h2 class="text-xl font-bold text-white mb-6 border-l-4 border-emerald-500 pl-3">Produk dari Toko Ini</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if($prods_q->num_rows == 0): ?>
            <div class="col-span-full py-10 text-center text-slate-400">Toko ini belum memiliki produk aktif.</div>
        <?php endif; ?>
        
        <?php while($row = $prods_q->fetch_assoc()): ?>
            <a href="product.php?id=<?= $row['id'] ?>" class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/10 transition-all group flex flex-col <?= $seller['store_status'] == 'closed' ? 'opacity-70' : '' ?>">
                <div class="h-40 w-full relative <?= $row['color_theme'] ?>">
                    <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-sm px-2 py-1 rounded text-xs font-bold text-white border border-slate-600/50"><?= $row['game'] ?></div>
                </div>
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="text-slate-200 font-semibold line-clamp-2 mb-2 group-hover:text-emerald-400 transition-colors"><?= $row['title'] ?></h3>
                    <div class="mt-auto">
                        <p class="text-emerald-500 font-bold text-lg"><?= formatRupiah($row['price']) ?></p>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-700">
                            <span class="text-xs text-slate-400 font-medium">Stok: <?= $row['stock'] ?></span>
                        </div>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
