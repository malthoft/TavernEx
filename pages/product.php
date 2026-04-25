<?php 
require_once '../config/db.php';

$product_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT p.*, u.username as seller_name, u.is_verified, u.store_status FROM products p JOIN users u ON p.seller_id = u.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if(!$product) {
    header("Location: ../index.php");
    exit;
}

// Store stats
$seller_id = $product['seller_id'];
$rating_q = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE seller_id = '$seller_id'");
$store_stats = $rating_q->fetch_assoc();
$avg_rating = number_format($store_stats['avg_rating'] ?? 0, 1);
$review_count = $store_stats['review_count'];

// Logic saat tombol tambah ke troli ditekan
if(isset($_POST['add_to_cart'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    
    if($_SESSION['role'] === 'buyer') {
        $buyer_id = $_SESSION['user_id'];
        // Check if already in cart
        $check = $conn->query("SELECT id FROM cart WHERE buyer_id='$buyer_id' AND product_id='$product_id'");
        if($check->num_rows == 0) {
            $conn->query("INSERT INTO cart (buyer_id, product_id, qty) VALUES ('$buyer_id', '$product_id', 1)");
            $add_success = "Produk berhasil ditambahkan ke troli!";
        } else {
            $add_error = "Produk ini sudah ada di troli Anda.";
        }
    } else {
        $error = "Hanya akun Pembeli yang dapat membeli barang.";
    }
}

// Handle Report
if(isset($_POST['report_product'])) {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $reason = trim($_POST['report_reason']);
    $reporter_id = $_SESSION['user_id'];
    
    $ins_report = $conn->prepare("INSERT INTO reports (product_id, reporter_id, reason, status) VALUES (?, ?, ?, 'pending')");
    $ins_report->bind_param("iis", $product_id, $reporter_id, $reason);
    if($ins_report->execute()){
        $report_success = "Laporan berhasil dikirim ke Admin.";
    }
}

// Fetch Similar Products
$cat_id = $product['category_id'] ?? 0;
$sim_stmt = $conn->prepare("SELECT p.*, u.username as seller_name, u.is_verified FROM products p JOIN users u ON p.seller_id = u.id WHERE p.category_id = ? AND p.id != ? ORDER BY RAND() LIMIT 4");
$sim_stmt->bind_param("ii", $cat_id, $product_id);
$sim_stmt->execute();
$similar_products = $sim_stmt->get_result();

require_once '../includes/header.php'; 
?>

<div class="max-w-5xl mx-auto px-4 py-8 w-full">
    <a href="../index.php" class="inline-flex items-center gap-2 text-slate-400 hover:text-white mb-6 transition-colors">
        <i class="ph ph-arrow-left"></i> Kembali ke Katalog
    </a>

    <?php if(isset($error) || isset($add_error)): ?>
        <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6"><?= $error ?? $add_error ?></div>
    <?php endif; ?>
    <?php if(isset($report_success) || isset($add_success)): ?>
        <div class="bg-emerald-500/20 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg mb-6"><?= $report_success ?? $add_success ?></div>
    <?php endif; ?>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden flex flex-col md:flex-row shadow-2xl">
        <div class="w-full md:w-2/5 h-80 md:h-auto overflow-hidden relative">
            <?php if($product['image_url']): ?>
                <img src="../<?= $product['image_url'] ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="w-full h-full <?= $product['color_theme'] ?>"></div>
            <?php endif; ?>
            <div class="absolute top-4 left-4 bg-emerald-500 text-slate-900 font-bold px-3 py-1 rounded-full text-xs shadow-lg uppercase tracking-wider">
                <?= $product['product_type'] ?>
            </div>
        </div>
        
        <div class="w-full md:w-3/5 p-6 md:p-8 flex flex-col">
            <div class="flex justify-between items-start mb-2">
                <span class="bg-slate-900 text-slate-300 px-3 py-1 rounded-full text-xs font-semibold border border-slate-700"><?= $product['game'] ?></span>
            </div>
            
            <h1 class="text-2xl md:text-3xl font-bold text-white mt-3 mb-2"><?= $product['title'] ?></h1>
            <div class="flex items-center gap-4 mb-4">
                <div class="text-3xl font-extrabold text-emerald-400"><?= formatRupiah($product['price']) ?></div>
                <div class="h-6 w-px bg-slate-700"></div>
                <div class="text-xs text-slate-400">
                    <span class="text-slate-200 font-bold"><?= $product['sold_count'] ?>+</span> Terjual
                </div>
                <div class="text-xs text-slate-400">
                    Stok: <span class="<?= ($product['stock'] == 0) ? 'text-red-500' : 'text-slate-200' ?> font-bold"><?= ($product['stock'] == -1) ? '∞ Unlimited' : $product['stock'] ?></span>
                </div>
            </div>

            <div class="bg-slate-900 p-4 rounded-xl border border-slate-700 flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="h-12 w-12 rounded-full bg-slate-700 flex items-center justify-center text-xl font-bold text-white relative">
                        <?= substr($product['seller_name'], 0, 1) ?>
                        <?php if($product['is_verified']): ?>
                            <div class="absolute -bottom-1 -right-1 bg-slate-900 rounded-full p-0.5">
                                <i class="ph-fill ph-seal-check text-emerald-500 text-sm"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-white font-bold inline-flex items-center gap-2">
                            <?= $product['seller_name'] ?>
                            <?php if($product['store_status'] == 'closed'): ?>
                                <span class="bg-red-500/20 text-red-400 text-[10px] px-2 py-0.5 rounded border border-red-500/30">Toko Tutup</span>
                            <?php else: ?>
                                <span class="bg-emerald-500/20 text-emerald-400 text-[10px] px-2 py-0.5 rounded border border-emerald-500/30">Toko Buka</span>
                            <?php endif; ?>
                        </p>
                        <p class="text-sm text-amber-500 font-medium flex items-center gap-1 mt-0.5">
                            <i class="ph-fill ph-star"></i> <?= $avg_rating ?> <span class="text-slate-500 text-xs">(<?= $review_count ?> Ulasan)</span>
                        </p>
                    </div>
                </div>
                <a href="store_profile.php?id=<?= $seller_id ?>" class="text-xs text-blue-400 hover:text-blue-300 font-bold border border-blue-400/30 px-3 py-1.5 rounded-lg hover:border-blue-400 transition">Kunjungi Toko</a>
            </div>

            <div class="mb-8">
                <h3 class="text-slate-300 font-semibold mb-2">Deskripsi Produk:</h3>
                <p class="text-slate-400 text-sm leading-relaxed">
                    <?= nl2br(htmlspecialchars($product['description'])) ?><br><br>
                    Sistem Login: <strong><?= htmlspecialchars($product['login_type']) ?></strong><br>
                    Proses via Escrow. Data akan dikirimkan oleh penjual ke grup chat TavernEx setelah pembayaran terkonfirmasi.
                </p>
            </div>

            <div class="mt-auto">
                <form method="POST" action="">
                    <?php if($product['store_status'] == 'closed' || !$product['is_active'] || $product['stock'] == 0): ?>
                        <div class="w-full bg-slate-700 text-slate-400 py-4 rounded-xl font-bold text-lg flex justify-center items-center gap-2 cursor-not-allowed">
                            <i class="ph-fill ph-prohibit"></i> <?= ($product['stock'] == 0) ? 'Stok Habis' : 'Tidak Dapat Dibeli' ?>
                        </div>
                    <?php else: ?>
                        <button type="submit" name="add_to_cart" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-xl font-extrabold text-lg transition-all shadow-lg shadow-orange-500/20 flex justify-center items-center gap-2">
                            <i class="ph-fill ph-shopping-cart"></i> Masukkan ke Troli
                        </button>
                    <?php endif; ?>
                </form>
                <div class="flex items-center justify-between mt-3">
                    <p class="text-xs text-slate-500 flex items-center gap-1">
                        <i class="ph-fill ph-lock-key"></i> Pembayaran Anda diamankan oleh TavernEx
                    </p>
                    <button onclick="document.getElementById('report-modal').classList.remove('hidden')" class="text-xs text-red-400 hover:text-red-300 font-semibold flex items-center gap-1">
                        <i class="ph-fill ph-flag"></i> Laporkan Produk
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Report -->
    <div id="report-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center items-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-white">Laporkan Produk Ini</h3>
                <button onclick="document.getElementById('report-modal').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="ph ph-x"></i></button>
            </div>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-slate-300 text-sm mb-2">Alasan Laporan:</label>
                    <textarea name="report_reason" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:outline-none focus:border-red-500" rows="3" required placeholder="Contoh: Deskripsi mencurigakan, indikasi penipuan..."></textarea>
                </div>
                <button type="submit" name="report_product" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-lg transition-colors">Kirim Laporan</button>
            </form>
        </div>
    </div>

    <?php if($similar_products->num_rows > 0): ?>
    <div class="mt-12">
        <h2 class="text-xl font-bold text-white mb-6 border-l-4 border-emerald-500 pl-3">Produk Serupa</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php while($row = $similar_products->fetch_assoc()): ?>
                <a href="product.php?id=<?= $row['id'] ?>" class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/10 transition-all group cursor-pointer flex flex-col">
                    <div class="h-32 w-full relative <?= $row['color_theme'] ?>">
                        <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-sm px-2 py-1 rounded text-xs font-bold text-white border border-slate-600/50"><?= $row['game'] ?></div>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-slate-200 font-semibold text-sm line-clamp-2 mb-2 group-hover:text-emerald-400 transition-colors"><?= $row['title'] ?></h3>
                        <div class="mt-auto">
                            <p class="text-emerald-500 font-bold mb-2"><?= formatRupiah($row['price']) ?></p>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-700 text-xs">
                                <span class="text-slate-400 font-medium"><?= $row['seller_name'] ?></span>
                                <?php if($row['is_verified']): ?>
                                    <i class="ph-fill ph-seal-check text-emerald-500" title="Terverifikasi"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>