<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

if(isset($_POST['toggle_active'])) {
    $pid = intval($_POST['prod_id']);
    $new_status = intval($_POST['new_status']);
    $conn->query("UPDATE products SET is_active='$new_status' WHERE id='$pid' AND seller_id='$seller_id'");
}

if(isset($_POST['delete_product'])) {
    $pid = intval($_POST['prod_id']);
    // Optional: Delete image
    $res = $conn->query("SELECT image_url FROM products WHERE id='$pid' AND seller_id='$seller_id'");
    $prod_data = $res->fetch_assoc();
    if($prod_data && $prod_data['image_url']) {
        @unlink("../" . $prod_data['image_url']);
    }
    $conn->query("DELETE FROM products WHERE id='$pid' AND seller_id='$seller_id'");
}

$seller_q = $conn->query("SELECT * FROM users WHERE id='$seller_id'");
$seller = $seller_q->fetch_assoc();

// Fetch products
$prods_q = $conn->query("SELECT * FROM products WHERE seller_id='$seller_id' ORDER BY id DESC");
$products = [];
while($row = $prods_q->fetch_assoc()) {
    $products[] = $row;
}

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto w-full flex-grow flex flex-col md:flex-row bg-slate-900 min-h-[calc(100vh-64px)]">
    <!-- Sidebar Itemku Style -->
    <div class="w-full md:w-64 bg-slate-800 border-r border-slate-700 flex-shrink-0">
        <div class="p-6 border-b border-slate-700 flex items-center gap-3">
            <div class="h-12 w-12 rounded-full bg-slate-700 flex items-center justify-center text-emerald-500 font-bold text-xl">
                <?= strtoupper(substr($seller['username'], 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <h3 class="font-bold text-white leading-tight truncate"><?= $seller['username'] ?></h3>
                <a href="store_profile.php?id=<?= $seller_id ?>" class="text-xs text-blue-400 bg-blue-400/10 px-2 py-0.5 rounded border border-blue-400/30 mt-1 inline-block hover:bg-blue-400/20 transition">Lihat Etalase</a>
            </div>
        </div>
        <div class="p-4 space-y-1 text-sm font-semibold">
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-4 px-2">Tokoku</div>
            <a href="seller_dashboard.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-house"></i> Beranda</a>
            <a href="store_settings.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-gear"></i> Pengaturan Toko</a>
            
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-6 px-2">Pesanan</div>
            <a href="seller_orders.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-receipt"></i> Riwayat Pesanan</a>
            
            <div class="text-slate-500 text-xs uppercase tracking-wider mb-2 mt-6 px-2">Dagangan</div>
            <a href="add_product.php" class="flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-slate-200 hover:bg-slate-700/50 rounded-lg transition"><i class="ph-fill ph-plus-circle"></i> Buat Dagangan</a>
            <a href="seller_products.php" class="flex items-center gap-3 px-3 py-2 text-emerald-400 bg-emerald-500/10 rounded-lg"><i class="ph-fill ph-list-dashes"></i> Daganganku</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow p-6 md:p-8 overflow-y-auto">
        <h1 class="text-2xl font-bold text-white mb-6 border-b border-slate-700 pb-2">Daganganku</h1>
        
        <div class="flex items-center gap-2 mb-6">
            <span class="text-slate-400 text-sm">Status toko:</span>
            <?php if($seller['store_status'] == 'open'): ?>
                <span class="text-emerald-500 font-bold text-sm">Buka</span>
            <?php else: ?>
                <span class="text-red-500 font-bold text-sm">Tutup</span>
            <?php endif; ?>
            <a href="store_settings.php" class="ml-auto text-blue-400 text-sm font-semibold flex items-center gap-1 hover:underline"><i class="ph-fill ph-pencil-simple"></i> Atur Toko</a>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-4 mb-6 sticky top-0 z-10">
            <div class="flex flex-wrap gap-4 items-center">
                <a href="add_product.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition">Tambah Produk</a>
                <div class="relative flex-grow max-w-xs">
                    <i class="ph ph-magnifying-glass absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" placeholder="Cari dagangan..." class="w-full bg-slate-900 border border-slate-600 rounded py-2 pl-9 pr-3 text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
            </div>
        </div>

        <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-900/50 border-b border-slate-700 text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Dagangan</th>
                            <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Harga</th>
                            <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                            <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50 text-slate-300">
                        <?php if(count($products) == 0): ?>
                            <tr><td colspan="4" class="px-6 py-12 text-center text-slate-500">Belum ada dagangan.</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach($products as $p): ?>
                            <tr class="hover:bg-slate-800/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg bg-slate-700 border border-slate-600 overflow-hidden flex-shrink-0">
                                            <?php if($p['image_url']): ?>
                                                <img src="../<?= $p['image_url'] ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-slate-500"><i class="ph ph-image text-2xl"></i></div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="product.php?id=<?= $p['id'] ?>" class="text-blue-400 font-bold hover:underline" target="_blank"><?= htmlspecialchars($p['title']) ?></a>
                                            <div class="text-[10px] text-slate-500 mt-0.5"><?= $p['game'] ?> • <?= $p['product_type'] ?> • Stok: <?= $p['stock'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-emerald-400"><?= formatRupiah($p['price']) ?></td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="prod_id" value="<?= $p['id'] ?>">
                                        <?php if($p['is_active']): ?>
                                            <input type="hidden" name="new_status" value="0">
                                            <button type="submit" name="toggle_active" class="bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase hover:bg-emerald-500/20 transition">Aktif</button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="1">
                                            <button type="submit" name="toggle_active" class="bg-slate-700 text-slate-400 border border-slate-600 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase hover:bg-slate-600 transition">Nonaktif</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                    <a href="edit_product.php?id=<?= $p['id'] ?>" class="p-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg transition" title="Edit"><i class="ph-bold ph-pencil-simple"></i></a>
                                    <form method="POST" class="inline" onsubmit="return confirm('Hapus dagangan ini permanen?')">
                                        <input type="hidden" name="prod_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="delete_product" class="p-2 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg transition border border-red-500/20" title="Hapus"><i class="ph-bold ph-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
        
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
