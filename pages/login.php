<?php 
require_once '../config/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, role, is_seller FROM users WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $pass);
    $stmt->execute();
    $user = stmt_fetch_assoc($stmt);

    if($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Always start as buyer unless admin
        if($user['role'] === 'admin') {
            $_SESSION['role'] = 'admin';
        } else {
            $_SESSION['role'] = 'buyer';
            $_SESSION['is_seller'] = ($user['role'] === 'seller' || $user['is_seller'] == 1);
        }
        
        header("Location: ../index.php");
        exit;
    } else {
        $error = "Akun tidak ditemukan! Gunakan data dummy.";
    }
}
require_once '../includes/header.php'; 
?>

<div class="min-h-[80vh] flex items-center justify-center p-4 w-full">
    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">
        <div class="text-center mb-8">
            <div class="inline-block bg-emerald-500 p-3 rounded-xl text-slate-900 mb-4">
                <i class="ph-fill ph-shield-check text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Masuk ke TavernEx</h2>
            <p class="text-slate-400 text-sm mt-2">Masuk untuk uji coba (Password: 123)</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-4 text-center text-sm border border-red-500/30"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-slate-400 text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-emerald-500 focus:outline-none" required placeholder="Masukkan username">
                <p class="text-xs text-slate-500 mt-1">Akun dummy: GuestBuyer_99, ProGamer_ID, Admin_Tavern</p>
            </div>
            <div class="mb-6">
                <label class="block text-slate-400 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white focus:border-emerald-500 focus:outline-none" required placeholder="Masukkan password (contoh: 123)">
            </div>
            <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all mb-4">Login Sistem</button>
        </form>

        <p class="text-center text-slate-400 text-sm">Belum punya akun? <a href="register.php" class="text-emerald-500 hover:text-emerald-400 font-bold">Daftar sekarang</a>.</p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>