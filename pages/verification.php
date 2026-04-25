<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check verification status
$stmt = $conn->prepare("SELECT is_verified FROM users WHERE id=?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if($user['is_verified']) {
    $already_verified = true;
} else {
    // Check if there is a pending request
    $req_stmt = $conn->prepare("SELECT status FROM verification_requests WHERE seller_id=? ORDER BY id DESC LIMIT 1");
    $req_stmt->bind_param("i", $seller_id);
    $req_stmt->execute();
    $latest_req = $req_stmt->get_result()->fetch_assoc();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && !$user['is_verified'] && (!isset($latest_req) || $latest_req['status'] !== 'pending')) {
    $wa = trim($_POST['whatsapp_number']);
    
    // Simulate KTP upload
    $ktp_image = "simulated_ktp_" . time() . ".jpg";
    
    $ins = $conn->prepare("INSERT INTO verification_requests (seller_id, ktp_image, whatsapp_number, status) VALUES (?, ?, ?, 'pending')");
    $ins->bind_param("iss", $seller_id, $ktp_image, $wa);
    if($ins->execute()) {
        $success = "Pengajuan verifikasi berhasil dikirim! Tim kami akan segera meninjaunya.";
        $latest_req = ['status' => 'pending'];
    } else {
        $error = "Terjadi kesalahan saat mengirim pengajuan.";
    }
}

require_once '../includes/header.php'; 
?>

<div class="max-w-3xl mx-auto px-4 py-12 w-full">
    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-slate-700 bg-slate-850 flex flex-col md:flex-row items-center gap-6">
            <div class="bg-amber-500/10 p-4 rounded-full border border-amber-500/20 text-amber-500 text-4xl flex-shrink-0">
                <i class="ph-fill ph-identification-card"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Verifikasi Identitas Penjual</h1>
                <p class="text-slate-400 mt-2 text-sm leading-relaxed">
                    Untuk menjamin keamanan transaksi (Escrow), semua penjual di TavernEx diwajibkan melakukan verifikasi KTP. 
                    Data Anda aman dan tidak akan dibagikan ke pihak ketiga.
                </p>
            </div>
        </div>

        <div class="p-8">
            <?php if(isset($already_verified) && $already_verified): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 p-6 rounded-xl flex items-center justify-center flex-col text-center">
                    <i class="ph-fill ph-check-circle text-emerald-500 text-6xl mb-4"></i>
                    <h2 class="text-xl font-bold text-white">Akun Anda Terverifikasi</h2>
                    <p class="text-slate-400 text-sm mt-2">Anda dapat berjualan dan menggunakan semua fitur TavernEx dengan bebas.</p>
                    <a href="add_product.php" class="mt-6 bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold px-6 py-2.5 rounded-lg transition-colors">
                        Mulai Jualan
                    </a>
                </div>
            <?php elseif(isset($latest_req) && $latest_req['status'] === 'pending'): ?>
                <div class="bg-blue-500/10 border border-blue-500/30 p-6 rounded-xl flex items-center justify-center flex-col text-center">
                    <i class="ph-fill ph-hourglass-high text-blue-500 text-6xl mb-4 animate-pulse"></i>
                    <h2 class="text-xl font-bold text-white">Pengajuan Sedang Diproses</h2>
                    <p class="text-slate-400 text-sm mt-2">Mohon tunggu 1x24 jam hingga Admin meninjau data Anda.</p>
                </div>
            <?php else: ?>
                
                <?php if(isset($latest_req) && $latest_req['status'] === 'rejected'): ?>
                    <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-xl mb-6 flex items-start gap-3">
                        <i class="ph-fill ph-warning-circle text-red-500 text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-red-400 font-bold text-sm">Pengajuan Sebelumnya Ditolak</p>
                            <p class="text-slate-400 text-xs mt-1">Silakan unggah kartu identitas yang lebih jelas.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(isset($success)): ?>
                    <div class="bg-emerald-500/20 text-emerald-400 p-3 rounded-lg mb-6 text-center text-sm border border-emerald-500/30"><?= $success ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-6 text-center text-sm border border-red-500/30"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-5">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Upload Foto KTP Asli</label>
                        <div class="border-2 border-dashed border-slate-600 rounded-xl p-8 text-center hover:bg-slate-700/50 transition-colors cursor-pointer relative group">
                            <i class="ph-fill ph-upload-simple text-3xl text-slate-400 group-hover:text-emerald-500 mb-2 transition-colors"></i>
                            <p class="text-sm font-bold text-white mb-1">Klik untuk upload atau drag & drop</p>
                            <p class="text-xs text-slate-500">PNG, JPG up to 5MB</p>
                            <input type="file" name="ktp_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                        </div>
                        <p class="text-xs text-emerald-500 mt-2 flex items-center gap-1"><i class="ph-fill ph-shield-check"></i> Proses upload disimulasikan di versi MVP ini.</p>
                    </div>

                    <div class="mb-8">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Nomor WhatsApp Aktif</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold">+62</span>
                            <input type="text" name="whatsapp_number" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 pr-4 pl-12 text-white focus:border-emerald-500 focus:outline-none" required placeholder="81234567890">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-500/20 transition-all flex justify-center items-center gap-2">
                        <i class="ph-fill ph-paper-plane-tilt"></i> Kirim Pengajuan Verifikasi
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
