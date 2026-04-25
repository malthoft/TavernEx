<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';

// Process Review
if(isset($_POST['submit_review'])) {
    $trx_id = $conn->real_escape_string($_POST['trx_id']);
    $product_id = intval($_POST['product_id']);
    $seller_id = intval($_POST['seller_id']);
    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);

    // Check if review exists
    $chk = $conn->query("SELECT id FROM reviews WHERE transaction_id='$trx_id'");
    if($chk->num_rows == 0) {
        $ins = $conn->prepare("INSERT INTO reviews (transaction_id, product_id, buyer_id, seller_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("siiiis", $trx_id, $product_id, $buyer_id, $seller_id, $rating, $comment);
        if($ins->execute()){
            $msg = "Terima kasih atas ulasannya!";
        }
    }
}

// Fetch orders with reviews
$q_str = "SELECT t.*, p.title, p.game, p.color_theme, p.image_url, p.seller_id, u.username as seller_name,
        (SELECT r.id FROM reviews r WHERE r.transaction_id = t.id LIMIT 1) as has_review
        FROM transactions t 
        JOIN products p ON t.product_id = p.id 
        JOIN users u ON p.seller_id = u.id 
        WHERE t.buyer_id = '$buyer_id'";

if($status_filter !== 'all') {
    $q_str .= " AND t.status = '$status_filter'";
}
$q_str .= " ORDER BY t.created_at DESC";

$orders_q = $conn->query($q_str);
$orders = [];
while($row = $orders_q->fetch_assoc()) {
    $orders[] = $row;
}

require_once '../includes/header.php'; 
?>

<div class="max-w-5xl mx-auto px-4 py-8 w-full flex-grow text-slate-300">
    <h1 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
        <i class="ph-fill ph-receipt text-emerald-500"></i> Riwayat Pesanan
    </h1>

    <?php if(isset($_GET['success'])): ?>
        <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl border border-emerald-500/30 font-semibold mb-6 flex items-center gap-3">
            <i class="ph-fill ph-check-circle text-2xl"></i> Pesanan berhasil dibuat! Sedang menunggu proses oleh penjual.
        </div>
    <?php endif; ?>
    <?php if(isset($msg)): ?>
        <div class="bg-blue-500/20 text-blue-400 p-4 rounded-xl border border-blue-500/30 font-semibold mb-6">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <!-- Nav Filter Itemku Style -->
    <div class="flex gap-2 overflow-x-auto pb-4 mb-6 sticky top-16 bg-[#0f172a] z-10 pt-2 border-b border-slate-700">
        <a href="?status=all" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg transition <?= $status_filter == 'all' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200' ?>">Semua</a>
        <a href="?status=pending" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg transition <?= $status_filter == 'pending' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200' ?>">Perlu Diproses</a>
        <a href="?status=processing" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg transition <?= $status_filter == 'processing' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200' ?>">Menunggu Konfirmasi</a>
        <a href="?status=completed" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg transition <?= $status_filter == 'completed' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200' ?>">Selesai</a>
        <a href="?status=cancelling" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg transition <?= $status_filter == 'cancelling' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200' ?>">Sedang Dibatalkan</a>
        <a href="?status=cancelled" class="whitespace-nowrap px-4 py-2 font-semibold text-sm rounded-lg transition <?= $status_filter == 'cancelled' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200' ?>">Pesanan Dibatalkan</a>
    </div>

    <!-- Order List -->
    <div class="space-y-4">
        <?php if(count($orders) == 0): ?>
            <div class="bg-slate-800 border border-slate-700 p-10 rounded-2xl text-center">
                <i class="ph ph-receipt text-6xl text-slate-600 mb-4 inline-block"></i>
                <h3 class="text-white font-bold text-lg">Belum ada pesanan</h3>
                <p class="text-slate-500 mt-2">Daftar transaksi kamu yang sesuai filter ini kosong.</p>
            </div>
        <?php endif; ?>

        <?php foreach($orders as $o): ?>
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 hover:border-slate-600 transition">
                <div class="flex justify-between items-center mb-4 border-b border-slate-700 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-white text-sm"><i class="ph-fill ph-storefront text-slate-400"></i> <?= $o['seller_name'] ?></span>
                        <span class="text-xs text-slate-500"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
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
                            'pending' => 'Perlu Diproses',
                            'processing' => 'Diproses Penjual (Escrow)',
                            'completed' => 'Selesai',
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
                        <h3 class="text-white font-bold mb-1"><?= $o['title'] ?></h3>
                        <p class="text-sm text-slate-400">Nomor Pesanan: <span class="font-bold text-slate-300"><?= $o['id'] ?></span></p>
                        <p class="text-sm text-slate-400">Pembayaran: <?= $o['payment_method'] ?></p>
                    </div>
                    <div class="text-right flex flex-col gap-2 min-w-[120px]">
                        <span class="text-slate-400 text-sm">Total Belanja</span>
                        <span class="text-emerald-400 font-extrabold text-lg"><?= formatRupiah($o['total_price']) ?></span>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700 flex flex-wrap justify-end gap-3">
                    <?php if($o['status'] == 'processing'): ?>
                        <a href="escrow.php?id=<?= $o['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition"><i class="ph-fill ph-chat-circle-text"></i> Chat Escrow</a>
                    <?php endif; ?>
                    <?php if($o['status'] == 'completed' && !$o['has_review']): ?>
                        <button onclick="openReviewModal('<?= $o['id'] ?>', '<?= $o['product_id'] ?>', '<?= $o['seller_id'] ?>')" class="bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold py-2 px-4 rounded-lg text-sm transition"><i class="ph-fill ph-star"></i> Beri Ulasan</button>
                    <?php elseif($o['status'] == 'completed' && $o['has_review']): ?>
                        <span class="text-emerald-500/80 font-bold text-sm px-4 py-2 border border-emerald-500/20 rounded-lg"><i class="ph-fill ph-check"></i> Ulasan Diberikan</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Review Modal -->
<div id="review-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex justify-center items-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white">Beri Ulasan</h3>
            <button onclick="document.getElementById('review-modal').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="trx_id" id="rev_trx_id">
            <input type="hidden" name="product_id" id="rev_prod_id">
            <input type="hidden" name="seller_id" id="rev_seller_id">
            
            <div class="mb-6 flex justify-center gap-2" id="star-container">
                <?php for($i=1; $i<=5; $i++): ?>
                    <button type="button" class="star-btn outline-none text-slate-600 hover:text-amber-500 transition-colors" data-value="<?= $i ?>">
                        <i class="ph-fill ph-star text-4xl"></i>
                    </button>
                <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rev_rating" value="5" required>
            
            <div class="mb-6">
                <label class="block text-slate-300 text-sm mb-2 font-bold">Komentar Pilihan (Opsional):</label>
                <textarea name="comment" class="w-full bg-slate-900 border border-slate-600 rounded-xl p-4 text-white focus:outline-none focus:border-amber-500 transition" rows="3" placeholder="Sangat memuaskan..."></textarea>
            </div>
            <button type="submit" name="submit_review" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold py-3 rounded-xl transition-colors text-lg">Kirim Ulasan</button>
        </form>
    </div>
</div>

<script>
    function openReviewModal(trxId, prodId, sellerId) {
        document.getElementById('rev_trx_id').value = trxId;
        document.getElementById('rev_prod_id').value = prodId;
        document.getElementById('rev_seller_id').value = sellerId;
        document.getElementById('review-modal').classList.remove('hidden');
        setRating(5);
    }

    const stars = document.querySelectorAll('.star-btn');
    stars.forEach(star => {
        star.addEventListener('click', (e) => {
            const val = e.currentTarget.getAttribute('data-value');
            setRating(val);
        });
    });

    function setRating(val) {
        document.getElementById('rev_rating').value = val;
        stars.forEach((s, idx) => {
            if(idx < val) {
                s.classList.remove('text-slate-600');
                s.classList.add('text-amber-500');
            } else {
                s.classList.add('text-slate-600');
                s.classList.remove('text-amber-500');
            }
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>
