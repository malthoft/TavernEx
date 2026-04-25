<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$buyer_name = $_SESSION['username'];
$selected = $_POST['selected_items'] ?? [];

if(empty($selected) && !isset($_POST['process_payment'])) {
    header("Location: cart.php");
    exit;
}

// Ensure array
if(!is_array($selected) && !empty($selected)) {
    $selected = [$selected];
}

$items = [];
$total_product_price = 0;

if(!isset($_POST['process_payment'])) {
    // Stage 1: View Checkout
    $in_clause = implode(',', array_map('intval', $selected));
    $query = "SELECT c.id as cart_id, p.*, u.username as seller_name FROM cart c JOIN products p ON c.product_id = p.id JOIN users u ON p.seller_id=u.id WHERE c.buyer_id='$buyer_id' AND c.id IN ($in_clause)";
    $res = $conn->query($query);
    while($row = $res->fetch_assoc()) {
        $items[] = $row;
        $total_product_price += $row['price'];
    }
} else {
    // Stage 2: Process Payment
    $payment_method = $_POST['payment_method'];
    $cart_ids = explode(',', $_POST['cart_ids_str']);
    
    foreach($cart_ids as $cid) {
        $cid = intval($cid);
        // GET item
        $q = $conn->query("SELECT product_id, p.price, p.seller_id, p.title FROM cart c JOIN products p ON c.product_id=p.id WHERE c.id='$cid' AND c.buyer_id='$buyer_id'");
        if($q->num_rows > 0) {
            $item = $q->fetch_assoc();
            $trx_id = "TRX-" . rand(100000, 999999);
            $total_paid = $item['price'] + calculateAdminFee($item['price']);
            
            // Insert trx
            $ins = $conn->prepare("INSERT INTO transactions (id, product_id, buyer_id, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?, 'pending')"); // Status pending initially for seller to process
            $ins->bind_param("siiis", $trx_id, $item['product_id'], $buyer_id, $total_paid, $payment_method);
            $ins->execute();
            
            // Auto Chat Msg
            $msg = "Pesanan baru telah dibayar menggunakan $payment_method.\nMohon Penjual segera memproses pesanan.";
            $ins_c = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, 'system', 'TradeGuard Bot', ?)");
            $ins_c->bind_param("ss", $trx_id, $msg);
            $ins_c->execute();
            
            // Notify seller
            $notif_msg = "Anda memiliki pesanan baru: ".$item['title'];
            $conn->query("INSERT INTO notifications (user_id, role, message, link) VALUES ('".$item['seller_id']."', 'seller', '$notif_msg', '/tavernex/pages/seller_orders.php')");

            // Delete cart
            $conn->query("DELETE FROM cart WHERE id='$cid'");
        }
    }
    
    // Redirect to buyer orders
    header("Location: buyer_orders.php?success=1");
    exit;
}

$admin_fee = 0;
foreach($items as $i) {
    $admin_fee += calculateAdminFee($i['price']);
}
$grand_total = $total_product_price + $admin_fee;

require_once '../includes/header.php'; 
?>

<div class="max-w-5xl mx-auto px-4 py-8 w-full flex-grow">
    <h1 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
        <i class="ph-fill ph-wallet text-blue-500"></i> Checkout & Pembayaran
    </h1>

    <div class="flex flex-col lg:flex-row gap-8">
        <div class="w-full lg:w-2/3 space-y-6">
            <!-- Review Items -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-white mb-4 border-b border-slate-700 pb-3">Daftar Pesanan</h2>
                <div class="space-y-4">
                    <?php foreach($items as $i): ?>
                        <div class="flex gap-4 items-center">
                            <div class="w-16 h-16 rounded-lg flex-shrink-0 <?= $i['color_theme'] ?>"></div>
                            <div class="flex-grow">
                                <p class="text-xs text-slate-400 font-semibold"><?= $i['seller_name'] ?></p>
                                <h3 class="text-white font-bold text-sm line-clamp-1"><?= $i['title'] ?></h3>
                                <p class="text-emerald-400 font-bold text-sm mt-1"><?= formatRupiah($i['price']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-white mb-4 border-b border-slate-700 pb-3">Metode Pembayaran</h2>
                <form id="checkout-form" action="" method="POST" class="space-y-3">
                    <input type="hidden" name="cart_ids_str" value="<?= implode(',', $selected) ?>">
                    
                    <label class="flex items-center justify-between p-4 border border-slate-600 rounded-xl cursor-pointer hover:border-emerald-500 hover:bg-slate-700/50 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="QRIS" class="h-4 w-4 text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-600" required>
                            <div>
                                <span class="text-white font-bold block">QRIS</span>
                                <span class="text-xs text-slate-400">Bayar otomatis via e-Wallet/M-Banking</span>
                            </div>
                        </div>
                        <i class="ph ph-qr-code text-2xl text-emerald-500"></i>
                    </label>
                    
                    <label class="flex items-center justify-between p-4 border border-slate-600 rounded-xl cursor-pointer hover:border-emerald-500 hover:bg-slate-700/50 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="Gopay" class="h-4 w-4 text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-600" required>
                            <span class="text-white font-bold">Gopay</span>
                        </div>
                        <span class="bg-blue-500/20 text-blue-400 text-xs px-2 py-1 rounded font-bold">Instan</span>
                    </label>
                    
                    <label class="flex items-center justify-between p-4 border border-slate-600 rounded-xl cursor-pointer hover:border-emerald-500 hover:bg-slate-700/50 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="DANA" class="h-4 w-4 text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-600" required>
                            <span class="text-white font-bold">DANA</span>
                        </div>
                        <span class="bg-blue-500/20 text-blue-400 text-xs px-2 py-1 rounded font-bold">Instan</span>
                    </label>
                    
                    <label class="flex items-center justify-between p-4 border border-slate-600 rounded-xl cursor-pointer hover:border-emerald-500 hover:bg-slate-700/50 transition">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="payment_method" value="Transfer Bank" class="h-4 w-4 text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-600" required>
                            <span class="text-white font-bold">Transfer Bank (Virtual Account)</span>
                        </div>
                        <i class="ph ph-bank text-xl text-slate-400"></i>
                    </label>
                </form>
            </div>
        </div>

        <div class="w-full lg:w-1/3 space-y-6">
            <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-5">
                <h3 class="text-emerald-400 font-bold mb-2 flex items-center gap-2"><i class="ph-fill ph-shield-check text-xl"></i> Aman 100% - Trade Guard</h3>
                <p class="text-xs text-emerald-500/80 leading-relaxed">Dana Anda akan ditahan oleh sistem Escrow TavernEx hingga pesanan diselesaikan dengan aman. Tidak ada risiko penipuan.</p>
            </div>

            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 shadow-lg">
                <h3 class="font-bold text-white mb-4">Detail Pembayaran</h3>
                <div class="space-y-2 text-sm mb-4 border-b border-slate-700 pb-4">
                    <div class="flex justify-between text-slate-300">
                        <span>Subtotal (<?= count($items) ?> Produk)</span>
                        <span><?= formatRupiah($total_product_price) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Biaya Trade Guard (Admin)</span>
                        <span><?= formatRupiah($admin_fee) ?></span>
                    </div>
                </div>
                <div class="flex justify-between items-end mb-6">
                    <span class="text-slate-300 font-bold">Total Pembayaran</span>
                    <span class="text-xl font-extrabold text-orange-500"><?= formatRupiah($grand_total) ?></span>
                </div>
                
                <!-- Simulasi Payment Gateway -> just submit -->
                <button type="submit" name="process_payment" form="checkout-form" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl transition-all shadow-lg shadow-emerald-500/20 text-lg flex items-center justify-center gap-2">
                    <i class="ph-fill ph-lock-key"></i> Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
