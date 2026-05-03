<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check verification status - fetch both is_verified AND is_seller
$stmt = $conn->prepare("SELECT is_verified, is_seller FROM users WHERE id=?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$user = stmt_fetch_assoc($stmt);

// Use strict integer comparison to prevent CPanel polyfill truthiness bugs
$already_verified = ($user && (int)$user['is_verified'] === 1 && (int)$user['is_seller'] === 1);

$latest_req = null;
if (!$already_verified) {
    // Check if there is a pending or approved request
    $req_stmt = $conn->prepare("SELECT status FROM verification_requests WHERE seller_id=? ORDER BY id DESC LIMIT 1");
    if ($req_stmt) {
        $req_stmt->bind_param("i", $seller_id);
        $req_stmt->execute();
        $latest_req = stmt_fetch_assoc($req_stmt);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$already_verified && (!$latest_req || $latest_req['status'] !== 'pending')) {
    $full_name = trim($_POST['full_name']);
    $nik_ktp = trim($_POST['nik_ktp']);
    $wa = trim($_POST['whatsapp_number']);
    
    $ins = $conn->prepare("INSERT INTO verification_requests (seller_id, full_name, nik_ktp, whatsapp_number, status) VALUES (?, ?, ?, ?, 'pending')");
    
    if ($ins) {
        $ins->bind_param("isss", $seller_id, $full_name, $nik_ktp, $wa);
        if($ins->execute()) {
            $success = "Pengajuan verifikasi berhasil dikirim! Tim kami akan segera meninjaunya.";
            $latest_req = ['status' => 'pending'];
        } else {
            $error = "Terjadi kesalahan saat menyimpan data pengajuan: " . $ins->error;
        }
    } else {
        $error = "Sistem gagal memproses: Struktur database belum di-update. Hubungi Admin atau jalankan update_db.php. (Detail: " . $conn->error . ")";
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
            <?php if($already_verified): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/30 p-6 rounded-xl flex items-center justify-center flex-col text-center">
                    <i class="ph-fill ph-check-circle text-emerald-500 text-6xl mb-4"></i>
                    <h2 class="text-xl font-bold text-white">Akun Anda Terverifikasi</h2>
                    <p class="text-slate-400 text-sm mt-2">Anda dapat berjualan dan menggunakan semua fitur TavernEx dengan bebas.</p>
                    <a href="add_product.php" class="mt-6 bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold px-6 py-2.5 rounded-lg transition-colors">
                        Mulai Jualan
                    </a>
                </div>
            <?php elseif($latest_req && $latest_req['status'] === 'pending'): ?>
                <div class="bg-blue-500/10 border border-blue-500/30 p-8 rounded-xl flex items-center justify-center flex-col text-center">
                    <i class="ph-fill ph-hourglass-high text-blue-500 text-6xl mb-4 animate-pulse"></i>
                    <h2 class="text-xl font-bold text-white">Menunggu Verifikasi Admin</h2>
                    <p class="text-slate-300 text-sm mt-3 max-w-md leading-relaxed">
                        Pengajuan Anda untuk menjadi <span class="text-amber-400 font-semibold">Penjual TavernEx</span> sudah kami terima dan sedang dalam antrian peninjauan oleh tim Admin.
                    </p>
                    <div class="mt-6 bg-slate-900/60 border border-slate-700 rounded-lg p-4 w-full max-w-sm text-left">
                        <div class="flex items-center gap-3 mb-3">
                            <i class="ph-fill ph-clock-countdown text-blue-400"></i>
                            <span class="text-slate-300 text-xs font-semibold">Estimasi Waktu: 1x24 Jam</span>
                        </div>
                        <div class="flex items-center gap-3 mb-3">
                            <i class="ph-fill ph-bell-ringing text-amber-400"></i>
                            <span class="text-slate-300 text-xs">Anda akan mendapat notifikasi setelah Admin memverifikasi data Anda.</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="ph-fill ph-info text-slate-400"></i>
                            <span class="text-slate-400 text-xs">Jangan mengirim pengajuan ulang, karena data Anda sudah masuk ke sistem.</span>
                        </div>
                    </div>
                </div>
            <?php elseif($latest_req && $latest_req['status'] === 'approved'): ?>
                <!-- Edge case: approved in verification_requests but user flags not yet updated -->
                <div class="bg-emerald-500/10 border border-emerald-500/30 p-6 rounded-xl flex items-center justify-center flex-col text-center">
                    <i class="ph-fill ph-check-circle text-emerald-500 text-6xl mb-4"></i>
                    <h2 class="text-xl font-bold text-white">Pengajuan Anda Disetujui!</h2>
                    <p class="text-slate-400 text-sm mt-2">Silakan logout dan login kembali untuk mengaktifkan fitur Seller Dashboard Anda.</p>
                </div>
            <?php else: ?>
                
                <?php if($latest_req && $latest_req['status'] === 'rejected'): ?>
                    <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-xl mb-6 flex items-start gap-3">
                        <i class="ph-fill ph-warning-circle text-red-500 text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-red-400 font-bold text-sm">Pengajuan Sebelumnya Ditolak</p>
                            <p class="text-slate-400 text-xs mt-1">Silakan perbaiki data identitas Anda dan coba ajukan kembali.</p>
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
                        <label class="block text-slate-300 text-sm font-semibold mb-2">Nama Lengkap (Sesuai KTP)</label>
                        <input type="text" name="full_name" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required placeholder="John Doe">
                    </div>

                    <div class="mb-5">
                        <label class="block text-slate-300 text-sm font-semibold mb-2">NIK KTP (16 Digit)</label>
                        <input type="text" name="nik_ktp" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required placeholder="1234567890123456" pattern="[0-9]{16}">
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
