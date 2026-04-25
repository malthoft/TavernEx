<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['save_settings'])) {
    $status = $_POST['store_status'] == 'open' ? 'open' : 'closed';
    $open_time = $_POST['open_time'];
    $close_time = $_POST['close_time'];

    $stmt = $conn->prepare("UPDATE users SET store_status=?, store_open_time=?, store_close_time=? WHERE id=?");
    $stmt->bind_param("sssi", $status, $open_time, $close_time, $user_id);
    if($stmt->execute()) {
        $msg = "Pengaturan toko berhasil disimpan.";
    }
}

// Fetch current
$stmt = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = $stmt->fetch_assoc();

require_once '../includes/header.php'; 
?>

<div class="max-w-4xl mx-auto px-4 py-10 w-full flex-grow">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full md:w-1/3">
            <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden text-center p-6 mb-6">
                <div class="h-16 w-16 mx-auto rounded-full bg-slate-700 flex items-center justify-center text-2xl font-bold text-emerald-500 mb-3">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <h2 class="text-lg font-bold text-white"><?= htmlspecialchars($user['username']) ?></h2>
            </div>
            <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden flex flex-col gap-1 p-2">
                <a href="profile.php" class="px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white font-medium rounded-xl flex items-center gap-3 transition"><i class="ph-fill ph-user text-xl"></i> Pengaturan Profil</a>
                <a href="store_settings.php" class="px-4 py-3 bg-emerald-500/10 text-emerald-400 font-bold rounded-xl flex items-center gap-3"><i class="ph-fill ph-storefront text-xl"></i> Pengaturan Toko</a>
                <a href="seller_orders.php" class="px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white font-medium rounded-xl flex items-center gap-3 transition"><i class="ph-fill ph-receipt text-xl"></i> Pesanan Masuk</a>
            </div>
        </div>

        <!-- Main Form -->
        <div class="w-full md:w-2/3 space-y-6">
            <?php if(isset($msg)): ?>
                <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl border border-emerald-500/30 font-semibold"><?= $msg ?></div>
            <?php endif; ?>

            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-xl">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i class="ph-fill ph-storefront"></i> Status & Jam Operasional</h3>
                <form method="POST" action="">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-slate-400 text-sm font-semibold mb-3">Status Toko Saat Ini</label>
                            <div class="flex gap-4">
                                <label class="flex-1 flex items-center justify-center gap-2 p-4 border border-slate-600 rounded-xl cursor-pointer hover:border-emerald-500 transition <?= $user['store_status'] == 'open' ? 'bg-emerald-500/10 border-emerald-500' : 'bg-slate-900' ?>">
                                    <input type="radio" name="store_status" value="open" class="hidden" <?= $user['store_status'] == 'open' ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <i class="ph-fill ph-door-open text-emerald-500 text-xl"></i>
                                    <span class="text-white font-bold">Buka</span>
                                </label>
                                <label class="flex-1 flex items-center justify-center gap-2 p-4 border border-slate-600 rounded-xl cursor-pointer hover:border-red-500 transition <?= $user['store_status'] == 'closed' ? 'bg-red-500/10 border-red-500' : 'bg-slate-900' ?>">
                                    <input type="radio" name="store_status" value="closed" class="hidden" <?= $user['store_status'] == 'closed' ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <i class="ph-fill ph-door text-red-500 text-xl"></i>
                                    <span class="text-white font-bold">Tutup</span>
                                </label>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Jika tutup, produk Anda tetap dapat dilihat di katalog namun tidak bisa dibeli.</p>
                        </div>

                        <div class="pt-6 border-t border-slate-700">
                            <label class="block text-slate-400 text-sm font-semibold mb-3">Jam Operasional Toko Otomatis</label>
                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <label class="text-xs text-slate-500 mb-1 block">Buka Jam</label>
                                    <input type="time" name="open_time" value="<?= substr($user['store_open_time'], 0, 5) ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                </div>
                                <span class="text-slate-500 font-bold mt-4">-</span>
                                <div class="flex-1">
                                    <label class="text-xs text-slate-500 mb-1 block">Tutup Jam</label>
                                    <input type="time" name="close_time" value="<?= substr($user['store_close_time'], 0, 5) ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-emerald-500">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-700">
                            <input type="hidden" name="save_settings" value="1">
                            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl transition-all">Simpan Pengaturan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
