<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Mark all as read when opening page
$conn->query("UPDATE notifications SET is_read=1 WHERE user_id='$user_id'");

$notifs_q = $conn->query("SELECT * FROM notifications WHERE user_id='$user_id' ORDER BY id DESC LIMIT 50");

require_once '../includes/header.php'; 
?>

<div class="max-w-4xl mx-auto px-4 py-8 w-full flex-grow">
    <div class="flex items-center gap-3 mb-6">
        <i class="ph-fill ph-bell-ringing text-3xl text-emerald-500"></i>
        <h1 class="text-2xl font-bold text-white tracking-tight">Pusat Notifikasi</h1>
    </div>

    <div class="bg-slate-850 rounded-2xl border border-slate-700 overflow-hidden shadow-xl">
        <?php if($notifs_q && $notifs_q->num_rows > 0): ?>
            <div class="divide-y divide-slate-700/50">
                <?php while($notif = $notifs_q->fetch_assoc()): ?>
                    <a href="<?= !empty($notif['link']) ? $notif['link'] : '#' ?>" class="block p-5 hover:bg-slate-800 transition-colors <?= $notif['is_read'] == 0 ? 'bg-slate-800/50' : '' ?>">
                        <div class="flex items-start gap-4">
                            <div class="mt-1 h-10 w-10 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                                <i class="ph-fill ph-info text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-slate-300 leading-relaxed"><?= htmlspecialchars($notif['message']) ?></p>
                                <span class="text-[10px] font-bold text-slate-500 mt-2 block uppercase tracking-wider">
                                    TavernEx System
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="p-12 text-center flex flex-col items-center justify-center">
                <i class="ph ph-bell-slash text-6xl text-slate-700 mb-4"></i>
                <h3 class="text-lg font-bold text-slate-300">Belum ada notifikasi</h3>
                <p class="text-sm text-slate-500 mt-2">Notifikasi baru akan muncul di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
