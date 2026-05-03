<?php 
require_once '../config/db.php';

$device_filter = $_GET['device'] ?? 'all';

$device_condition = "";
if($device_filter == 'mobile') $device_condition = "WHERE device_type IN ('mobile', 'both')";
if($device_filter == 'pc') $device_condition = "WHERE device_type IN ('pc', 'both')";

$cats_stmt = $conn->query("SELECT * FROM categories $device_condition ORDER BY name ASC");
$categories = [];
while($cat = $cats_stmt->fetch_assoc()) {
    $categories[] = $cat;
}

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
    <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Jelajahi Semua Game</h1>
            <p class="text-slate-400 mt-1">Temukan produk impian Anda dari berbagai game populer.</p>
        </div>
        
        <div class="flex bg-slate-800 p-1 rounded-xl border border-slate-700">
            <a href="?device=all" class="px-6 py-2 rounded-lg text-sm font-bold transition-all <?= ($device_filter == 'all') ? 'bg-emerald-500 text-slate-900 shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white' ?>">Semua</a>
            <a href="?device=mobile" class="px-6 py-2 rounded-lg text-sm font-bold transition-all <?= ($device_filter == 'mobile') ? 'bg-emerald-500 text-slate-900 shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white' ?>">Mobile</a>
            <a href="?device=pc" class="px-6 py-2 rounded-lg text-sm font-bold transition-all <?= ($device_filter == 'pc') ? 'bg-emerald-500 text-slate-900 shadow-lg shadow-emerald-500/20' : 'text-slate-400 hover:text-white' ?>">PC Game</a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        <?php foreach($categories as $cat): ?>
            <a href="../index.php?category=<?= $cat['id'] ?>" class="group block relative rounded-2xl hover:-translate-y-2 transition-all duration-300">
                <div class="w-full aspect-[3/4] bg-gradient-to-br from-slate-700 to-slate-850 rounded-2xl flex items-center justify-center border border-slate-600 group-hover:border-emerald-500/50 shadow-xl relative overflow-hidden">
                    <?php if($cat['image_url']): ?>
                        <img src="<?= strpos($cat['image_url'], 'http') === 0 || strpos($cat['image_url'], 'data:') === 0 ? $cat['image_url'] : '../' . $cat['image_url'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <i class="ph-fill ph-game-controller text-5xl text-slate-500 group-hover:text-emerald-400 transition-colors relative z-10"></i>
                    <?php endif; ?>
                    <div class="absolute bottom-0 left-0 right-0 h-1/2 bg-gradient-to-t from-slate-900 via-slate-900/60 to-transparent"></div>
                    
                    <div class="absolute bottom-4 left-0 right-0 px-4 text-center">
                        <div class="text-sm font-bold text-white truncate"><?= $cat['name'] ?></div>
                        <div class="flex justify-center gap-1.5 mt-2">
                            <?php if($cat['device_type'] == 'mobile' || $cat['device_type'] == 'both'): ?>
                                <i class="ph-fill ph-device-mobile text-[12px] text-slate-400 bg-slate-800/80 p-1 rounded"></i>
                            <?php endif; ?>
                            <?php if($cat['device_type'] == 'pc' || $cat['device_type'] == 'both'): ?>
                                <i class="ph-fill ph-desktop text-[12px] text-slate-400 bg-slate-800/80 p-1 rounded"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../includes/header.php'; ?>
