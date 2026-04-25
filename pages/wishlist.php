<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Handle Remove from Wishlist
if(isset($_POST['remove_wishlist'])) {
    $prod_id = (int)$_POST['product_id'];
    $conn->query("DELETE FROM wishlists WHERE buyer_id='$buyer_id' AND product_id='$prod_id'");
}

// Fetch wishlist products
$query = "SELECT p.*, u.username as seller_name, u.is_verified FROM wishlists w JOIN products p ON w.product_id = p.id JOIN users u ON p.seller_id = u.id WHERE w.buyer_id = '$buyer_id' ORDER BY w.created_at DESC";
$result = $conn->query($query);

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow">
    <h1 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
        <i class="ph-fill ph-heart text-red-500"></i> Wishlist Saya
    </h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php if($result->num_rows == 0): ?>
            <div class="col-span-full py-20 text-center bg-slate-800 rounded-2xl border border-slate-700">
                <i class="ph ph-heart-break text-6xl text-slate-600 mb-4 inline-block"></i>
                <h3 class="text-white font-bold text-lg">Wishlist Kosong</h3>
                <p class="text-slate-500 mt-2">Anda belum menambahkan produk favorit ke sini.</p>
                <a href="../index.php" class="mt-6 inline-block bg-emerald-500 text-slate-900 font-bold px-6 py-2 rounded-lg">Mulai Belanja</a>
            </div>
        <?php endif; ?>
        
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/10 transition-all group flex flex-col relative">
                <form method="POST" class="absolute top-2 right-2 z-20">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="remove_wishlist" class="w-8 h-8 rounded-full bg-red-500/10 backdrop-blur-md flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition-colors border border-red-500/20">
                        <i class="ph-bold ph-trash"></i>
                    </button>
                </form>

                <a href="/tavernex/pages/product.php?id=<?= $row['id'] ?>" class="flex flex-col h-full">
                    <div class="h-40 w-full relative overflow-hidden">
                        <?php if($row['image_url']): ?>
                            <img src="../<?= $row['image_url'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <div class="w-full h-full <?= $row['color_theme'] ?>"></div>
                        <?php endif; ?>
                        <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-sm px-2 py-1 rounded text-[10px] font-bold text-white border border-slate-600/50 uppercase"><?= $row['game'] ?></div>
                    </div>
                    
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-slate-200 font-semibold line-clamp-2 mb-2 group-hover:text-emerald-400 transition-colors h-10"><?= $row['title'] ?></h3>
                        <div class="mt-auto">
                            <p class="text-emerald-500 font-bold text-lg"><?= formatRupiah($row['price']) ?></p>
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] text-slate-400 font-medium"><?= $row['seller_name'] ?></span>
                                </div>
                                <?php if($row['is_verified']): ?>
                                    <i class="ph-fill ph-seal-check text-emerald-500 text-sm"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
