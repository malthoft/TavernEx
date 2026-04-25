<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];

// Remove from cart
if(isset($_POST['remove_id'])) {
    $remove_id = $conn->real_escape_string($_POST['remove_id']);
    $conn->query("DELETE FROM cart WHERE id='$remove_id' AND buyer_id='$buyer_id'");
}

// Fetch Cart Items
$query = "SELECT c.id as cart_id, p.*, u.username as seller_name FROM cart c JOIN products p ON c.product_id = p.id JOIN users u ON p.seller_id=u.id WHERE c.buyer_id='$buyer_id' ORDER BY c.id DESC";
$cart_items = $conn->query($query);
$items = [];
while($row = $cart_items->fetch_assoc()) {
    $items[] = $row;
}
$is_empty = count($items) == 0;

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow">
    <h1 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
        <i class="ph-fill ph-shopping-cart text-orange-500"></i> Troli Belanja
    </h1>

    <?php if($is_empty): ?>
        <div class="bg-slate-800 rounded-2xl p-10 text-center border border-slate-700 mt-10">
            <i class="ph-fill ph-bag text-6xl text-slate-600 mb-4 inline-block"></i>
            <h2 class="text-xl font-bold text-white mb-2">Troli kamu masih kosong</h2>
            <p class="text-slate-400 mb-6">Yuk temukan game favoritmu sekarang!</p>
            <a href="../index.php?view=catalog" class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-2.5 px-6 rounded-lg transition-colors inline-block">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <form action="checkout.php" method="POST" class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-2/3 space-y-4">
                <?php foreach($items as $i): ?>
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex gap-4 items-center">
                        <input type="checkbox" name="selected_items[]" value="<?= $i['cart_id'] ?>" class="h-5 w-5 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500 bg-slate-900 cursor-pointer" checked>
                        <div class="w-20 h-20 rounded-lg flex-shrink-0 <?= $i['color_theme'] ?>"></div>
                        <div class="flex-grow">
                            <p class="text-xs text-slate-400 font-semibold mb-1"><?= $i['seller_name'] ?></p>
                            <h3 class="text-white font-bold text-sm md:text-base line-clamp-2"><?= $i['title'] ?></h3>
                            <p class="text-emerald-400 font-bold mt-1"><?= formatRupiah($i['price']) ?></p>
                        </div>
                        <button type="submit" name="remove_id" value="<?= $i['cart_id'] ?>" class="text-slate-500 hover:text-red-400 p-2" title="Hapus" formmethod="POST" formaction="">
                            <i class="ph-fill ph-trash text-xl"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="w-full lg:w-1/3">
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 sticky top-24 shadow-lg shadow-slate-900/50">
                    <h3 class="font-bold text-white mb-4">Ringkasan Belanja</h3>
                    <p class="text-sm text-slate-400 mb-6">Pilih item di samping yang ingin Anda checkout sekarang.</p>
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl transition-all shadow-lg shadow-emerald-500/20 text-lg">Beli Sekarang</button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
