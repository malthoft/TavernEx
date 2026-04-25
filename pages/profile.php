<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user
$stmt = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = $stmt->fetch_assoc();

if(isset($_POST['update_profile'])) {
    $new_username = $conn->real_escape_string($_POST['username']);
    
    // Check if new password is provided
    if(!empty($_POST['new_password'])) {
        $new_password = $_POST['new_password']; // In production: password_hash
        $conn->query("UPDATE users SET username='$new_username', password='$new_password' WHERE id='$user_id'");
    } else {
        $conn->query("UPDATE users SET username='$new_username' WHERE id='$user_id'");
    }
    
    $_SESSION['username'] = $new_username;
    $msg = "Profil berhasil diperbarui.";
}

// Handle role switching
if(isset($_POST['switch_role'])) {
    $target_role = $_POST['target_role'];
    if($target_role === 'seller' && ($user['role'] === 'seller' || $user['is_seller'] == 1)) {
        $_SESSION['role'] = 'seller';
        header("Location: seller_dashboard.php");
        exit;
    } elseif($target_role === 'buyer') {
        $_SESSION['role'] = 'buyer';
        header("Location: ../index.php");
        exit;
    }
}

require_once '../includes/header.php'; 
?>

<div class="max-w-4xl mx-auto px-4 py-10 w-full flex-grow">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full md:w-1/3">
            <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden text-center p-6">
                <div class="h-24 w-24 mx-auto rounded-full bg-slate-700 flex items-center justify-center text-4xl font-bold text-emerald-500 mb-4 border-4 border-slate-900 shadow-xl">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <h2 class="text-xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h2>
                <span class="inline-block mt-2 px-3 py-1 bg-amber-500/20 text-amber-500 text-xs font-bold uppercase tracking-wider rounded-full"><?= $user['role'] ?></span>
            </div>

            <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden mt-6 flex flex-col gap-1 p-2">
                <a href="profile.php" class="px-4 py-3 bg-emerald-500/10 text-emerald-400 font-bold rounded-xl flex items-center gap-3"><i class="ph-fill ph-user text-xl"></i> Pengaturan Profil</a>
                
                <?php if($_SESSION['role'] == 'seller'): ?>
                    <a href="seller_dashboard.php" class="px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white font-medium rounded-xl flex items-center gap-3 transition"><i class="ph-fill ph-house text-xl"></i> Dashboard Penjual</a>
                    <form method="POST" action="">
                        <input type="hidden" name="target_role" value="buyer">
                        <button type="submit" name="switch_role" class="w-full px-4 py-3 text-amber-400 hover:bg-amber-500/10 font-bold rounded-xl flex items-center gap-3 transition text-left"><i class="ph-fill ph-swap text-xl"></i> Pindah ke Mode Pembeli</button>
                    </form>
                <?php elseif($_SESSION['role'] == 'buyer'): ?>
                    <a href="buyer_orders.php" class="px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white font-medium rounded-xl flex items-center gap-3 transition"><i class="ph-fill ph-receipt text-xl"></i> Riwayat Pesanan</a>
                    
                    <?php if($user['is_seller'] || $user['role'] == 'seller'): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="target_role" value="seller">
                            <button type="submit" name="switch_role" class="w-full px-4 py-3 text-emerald-400 hover:bg-emerald-500/10 font-bold rounded-xl flex items-center gap-3 transition text-left"><i class="ph-fill ph-swap text-xl"></i> Pindah ke Mode Penjual</button>
                        </form>
                    <?php else: ?>
                        <a href="verification.php" class="px-4 py-3 text-amber-400 hover:bg-amber-500/10 font-bold rounded-xl flex items-center gap-3 transition"><i class="ph-fill ph-storefront text-xl"></i> Daftar Jadi Penjual</a>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="help.php" class="px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white font-medium rounded-xl flex items-center gap-3 transition mt-2"><i class="ph-fill ph-question text-xl"></i> Pusat Bantuan</a>
                
                <div class="h-px bg-slate-700 my-2"></div>
                
                <button onclick="confirmLogout()" class="px-4 py-3 text-red-400 hover:bg-red-500/10 font-bold rounded-xl flex items-center gap-3 transition text-left"><i class="ph-fill ph-sign-out text-xl"></i> Keluar (Logout)</button>
            </div>
        </div>

        <!-- Main Form -->
        <div class="w-full md:w-2/3">
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-8 shadow-xl">
                <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2"><i class="ph-fill ph-gear"></i> Informasi Akun</h3>
                
                <?php if(isset($msg)): ?>
                    <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl border border-emerald-500/30 font-semibold mb-6"><?= $msg ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-slate-400 text-sm font-semibold mb-2">Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500 transition" required>
                        </div>
                        <div>
                            <label class="block text-slate-400 text-sm font-semibold mb-2">Password Baru <span class="text-xs font-normal text-slate-500">(Kosongi jika tidak diubah)</span></label>
                            <input type="password" name="new_password" placeholder="••••••••" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500 transition">
                        </div>
                        <div class="pt-4 border-t border-slate-700">
                            <button type="submit" name="update_profile" class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-emerald-500/20">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmLogout() {
        if(confirm('Apakah Anda yakin ingin keluar dari akun ini?')) {
            window.location.href = 'logout.php';
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
