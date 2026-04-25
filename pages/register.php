<?php 
require_once '../config/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $pass = $_POST['password']; // In a real system, you'd use password_hash()

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $is_verified = ($role === 'buyer') ? 1 : 0; // Buyer is verified by default, Seller needs manual KTP validation
        $ins = $conn->prepare("INSERT INTO users (username, password, role, is_verified) VALUES (?, ?, ?, ?)");
        $ins->bind_param("sssi", $username, $pass, $role, $is_verified);
        if($ins->execute()) {
            $success = "Akun berhasil dibuat! Silakan login.";
        } else {
            $error = "Gagal mendaftar.";
        }
    }
}
require_once '../includes/header.php'; 
?>

<div class="min-h-[80vh] flex items-center justify-center p-4 w-full">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">
        <div class="text-center mb-8">
            <div class="inline-block bg-emerald-500 p-3 rounded-xl text-slate-900 mb-4">
                <i class="ph-fill ph-user-plus text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Daftar ke TavernEx</h2>
            <p class="text-slate-400 text-sm mt-2">Buat akun untuk bertransaksi dengan aman.</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4 text-center text-sm border border-red-500/30"><?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <div class="bg-emerald-500/20 text-emerald-400 p-3 rounded-lg mb-4 text-center text-sm border border-emerald-500/30">
                <?= $success ?>
                <br>
                <a href="login.php" class="font-bold underline mt-2 inline-block">Klik di sini untuk Login</a>
            </div>
        <?php else: ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-slate-400 text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-emerald-500 focus:outline-none" required placeholder="GamerName123">
            </div>
            
            <div class="mb-4">
                <label class="block text-slate-400 text-sm font-semibold mb-2">Sebagai Apa?</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="buyer" class="peer sr-only" checked>
                        <div class="rounded-lg border border-slate-600 bg-slate-900 p-4 hover:bg-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 text-center transition-all">
                            <i class="ph-fill ph-shopping-bag text-2xl text-slate-300 peer-checked:text-emerald-500 mb-2 block mx-auto"></i>
                            <span class="text-white font-bold block">Pembeli</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="seller" class="peer sr-only">
                        <div class="rounded-lg border border-slate-600 bg-slate-900 p-4 hover:bg-slate-700 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 text-center transition-all">
                            <i class="ph-fill ph-storefront text-2xl text-slate-300 peer-checked:text-emerald-500 mb-2 block mx-auto"></i>
                            <span class="text-white font-bold block">Penjual</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-slate-400 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-emerald-500 focus:outline-none" required placeholder="••••••••">
            </div>
            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all mb-4">Daftar Sekarang</button>
        </form>
        
        <?php endif; ?>

        <p class="text-center text-slate-400 text-sm">Sudah punya akun? <a href="login.php" class="text-emerald-500 hover:text-emerald-400 font-bold">Masuk di sini</a>.</p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
