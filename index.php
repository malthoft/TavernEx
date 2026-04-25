<?php 
require_once 'config/db.php';

// Redirect based on role if no view parameter is set
if(empty($_GET['view']) && isset($_SESSION['role'])) {
    if($_SESSION['role'] === 'admin') {
        header("Location: pages/admin_dashboard.php");
        exit;
    } elseif($_SESSION['role'] === 'seller') {
        header("Location: pages/seller_dashboard.php");
        exit;
    }
}


$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$query = "SELECT p.*, u.username as seller_name, u.is_verified FROM products p JOIN users u ON p.seller_id = u.id WHERE p.is_active=1";

if(!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $query .= " AND p.title LIKE '%$search_safe%'";
}

if(!empty($category_filter)) {
    $cat_safe = $conn->real_escape_string($category_filter);
    $query .= " AND p.category_id = '$cat_safe'";
}

if(!empty($min_price)) {
    $min_safe = (int)$min_price;
    $query .= " AND p.price >= $min_safe";
}

if(!empty($max_price)) {
    $max_safe = (int)$max_price;
    $query .= " AND p.price <= $max_safe";
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$query .= " ORDER BY p.id DESC";

// Get total for pagination
$total_query = str_replace("p.*, u.username as seller_name, u.is_verified", "COUNT(*) as total", $query);
$total_res = $conn->query($total_query);
$total_rows = $total_res->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query .= " LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Handle Wishlist Toggle
if(isset($_POST['toggle_wishlist']) && isset($_SESSION['user_id'])) {
    $buyer_id = $_SESSION['user_id'];
    $prod_id = (int)$_POST['product_id'];
    
    $check = $conn->query("SELECT id FROM wishlists WHERE buyer_id='$buyer_id' AND product_id='$prod_id'");
    if($check->num_rows > 0) {
        $conn->query("DELETE FROM wishlists WHERE buyer_id='$buyer_id' AND product_id='$prod_id'");
    } else {
        $conn->query("INSERT INTO wishlists (buyer_id, product_id) VALUES ('$buyer_id', '$prod_id')");
    }
}

// Fetch user's wishlist IDs
$user_wishlist = [];
if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $wl_res = $conn->query("SELECT product_id FROM wishlists WHERE buyer_id='$uid'");
    while($wl = $wl_res->fetch_assoc()) {
        $user_wishlist[] = $wl['product_id'];
    }
}

// Fetch categories for the filter dropdown
$device_filter = $_GET['device'] ?? 'all';
$cat_limit_sql = (empty($search) && empty($category_filter) && empty($_GET['view_all_games'])) ? "LIMIT 6" : "";

$device_condition = "";
if($device_filter == 'mobile') $device_condition = "WHERE device_type IN ('mobile', 'both')";
if($device_filter == 'pc') $device_condition = "WHERE device_type IN ('pc', 'both')";

$cats_stmt = $conn->query("SELECT * FROM categories $device_condition ORDER BY name ASC $cat_limit_sql");
$categories = [];
while($cat = $cats_stmt->fetch_assoc()) {
    $categories[] = $cat;
}

require_once 'includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-grow">
    <div class="bg-gradient-to-r from-emerald-900 to-slate-800 rounded-2xl p-6 md:p-8 mb-8 border border-emerald-800/50 relative overflow-hidden flex flex-col md:flex-row items-center justify-between">
        <div class="relative z-10 max-w-xl">
            <h1 class="text-2xl font-bold text-white mb-2">Transaksi Game Aman dengan <span class="text-emerald-400">Sistem Escrow</span></h1>
            <p class="text-sm text-slate-300 mb-4">Dana ditahan sistem hingga data game tervalidasi. 100% Anti Hackback & Penipuan.</p>
            <div class="flex gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-semibold border border-emerald-500/30">
                    <i class="ph-fill ph-check-circle"></i> Verifikasi KTP
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 text-xs font-semibold border border-amber-500/30">
                    <i class="ph-fill ph-headset"></i> Live Midman
                </span>
            </div>
        </div>
        <i class="ph-fill ph-game-controller text-[120px] text-emerald-500/10 absolute -right-6 -bottom-5 transform rotate-12"></i>
    </div>

    <div class="mb-8">
        <form method="GET" action="" class="flex flex-col gap-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-grow relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-3.5 text-slate-400 text-lg"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari akun atau item game..." class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl py-3 pl-12 pr-4 focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <div class="relative min-w-[200px]">
                    <select name="category" class="w-full bg-slate-800 border border-slate-700 text-white rounded-xl py-3 pl-4 pr-10 appearance-none focus:outline-none focus:border-emerald-500 transition-colors">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category_filter == $cat['id']) ? 'selected' : '' ?>><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ph ph-caret-down absolute right-4 top-3.5 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <input type="number" name="min_price" value="<?= htmlspecialchars($min_price) ?>" placeholder="Harga Min" class="w-full md:w-32 bg-slate-800 border border-slate-700 text-white rounded-xl py-2 px-4 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                    <span class="text-slate-500">-</span>
                    <input type="number" name="max_price" value="<?= htmlspecialchars($max_price) ?>" placeholder="Harga Max" class="w-full md:w-32 bg-slate-800 border border-slate-700 text-white rounded-xl py-2 px-4 text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                </div>
                <button type="submit" class="w-full md:w-auto bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-2.5 px-8 rounded-xl transition-colors whitespace-nowrap ml-auto">
                    Cari Produk
                </button>
            </div>
        </form>
    </div>

    <?php if(empty($search) && empty($category_filter)): ?>
        <!-- Itemku Style Quick Buy / Beli Cepat -->
        <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700">
            <div class="flex items-center gap-3 mb-6">
                <div class="bg-amber-500/20 p-2 rounded-lg text-amber-500">
                    <i class="ph-fill ph-lightning text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-white">Beli Cepat</h2>
            </div>
            
            <div class="flex flex-wrap gap-2 mb-6 border-b border-slate-700 pb-4" id="device-filter">
                <a href="?device=all" class="<?= ($device_filter == 'all') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' ?> px-4 py-1.5 rounded text-sm font-semibold border border-slate-600 transition">For You</a>
                <a href="?device=mobile" class="<?= ($device_filter == 'mobile') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' ?> px-3 py-1.5 rounded text-sm border border-transparent hover:border-slate-700 transition">Mobile</a>
                <a href="?device=pc" class="<?= ($device_filter == 'pc') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' ?> px-3 py-1.5 rounded text-sm border border-transparent hover:border-slate-700 transition">PC Game</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach($categories as $cat): ?>
                    <a href="?category=<?= $cat['id'] ?>" class="group block relative rounded-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="w-full aspect-square bg-gradient-to-br from-slate-700 to-slate-850 rounded-xl flex items-center justify-center border border-slate-600 group-hover:border-emerald-500/50 shadow-lg relative overflow-hidden">
                            <?php if($cat['image_url']): ?>
                                <img src="<?= $cat['image_url'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <i class="ph-fill ph-game-controller text-4xl text-slate-500 group-hover:text-emerald-400 transition-colors relative z-10"></i>
                            <?php endif; ?>
                            <div class="absolute bottom-0 left-0 right-0 h-1/2 bg-gradient-to-t from-slate-900 to-transparent"></div>
                            <!-- Device Icon Badge -->
                            <div class="absolute top-2 right-2 flex gap-1">
                                <?php if($cat['device_type'] == 'mobile' || $cat['device_type'] == 'both'): ?>
                                    <i class="ph-fill ph-device-mobile text-[10px] bg-slate-900/80 text-slate-400 p-1 rounded-sm"></i>
                                <?php endif; ?>
                                <?php if($cat['device_type'] == 'pc' || $cat['device_type'] == 'both'): ?>
                                    <i class="ph-fill ph-desktop text-[10px] bg-slate-900/80 text-slate-400 p-1 rounded-sm"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-sm font-bold text-slate-300 group-hover:text-white"><?= $cat['name'] ?></div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if(empty($cat_limit_sql) == false): ?>
                <div class="mt-8 text-center">
                    <a href="pages/browse_games.php" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-xl font-bold transition-all border border-slate-600 shadow-lg">
                        Lihat Semua Game <i class="ph ph-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- Products Grid -->
        <h2 class="text-lg font-bold text-white mb-4">Hasil Pencarian</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if($result->num_rows == 0): ?>
                <div class="col-span-full py-10 text-center text-slate-400">Tidak ada produk ditemukan.</div>
            <?php endif; ?>
            
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/10 transition-all group flex flex-col relative">
                    <!-- Wishlist Button -->
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'buyer'): ?>
                        <form method="POST" class="absolute top-2 right-2 z-20">
                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="toggle_wishlist" class="w-8 h-8 rounded-full bg-slate-900/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-emerald-500 transition-colors">
                                <i class="<?= in_array($row['id'], $user_wishlist) ? 'ph-fill ph-heart text-red-500' : 'ph ph-heart' ?>"></i>
                            </button>
                        </form>
                    <?php endif; ?>

                    <a href="/tavernex/pages/product.php?id=<?= $row['id'] ?>" class="flex flex-col h-full">
                        <div class="h-40 w-full relative overflow-hidden">
                            <?php if($row['image_url']): ?>
                                <img src="<?= $row['image_url'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <div class="w-full h-full <?= $row['color_theme'] ?>"></div>
                            <?php endif; ?>
                            <div class="absolute top-2 left-2 bg-slate-900/80 backdrop-blur-sm px-2 py-1 rounded text-[10px] font-bold text-white border border-slate-600/50 uppercase"><?= $row['game'] ?></div>
                            <div class="absolute bottom-2 left-2 bg-emerald-500 text-slate-900 px-2 py-0.5 rounded text-[10px] font-bold"><?= $row['product_type'] ?></div>
                        </div>
                        
                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="text-slate-200 font-semibold line-clamp-2 mb-2 group-hover:text-emerald-400 transition-colors h-10"><?= $row['title'] ?></h3>
                            <div class="mt-auto">
                                <p class="text-emerald-500 font-bold text-lg"><?= formatRupiah($row['price']) ?></p>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-700">
                                    <div class="flex items-center gap-1.5">
                                        <div class="h-6 w-6 rounded-full bg-slate-600 flex items-center justify-center text-[10px] font-bold text-white">
                                            <?= substr($row['seller_name'], 0, 1) ?>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-medium truncate max-w-[60px]"><?= $row['seller_name'] ?></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] text-slate-500"><?= $row['sold_count'] ?>+ terjual</span>
                                        <?php if($row['is_verified']): ?>
                                            <i class="ph-fill ph-seal-check text-emerald-500 text-sm" title="Penjual Terverifikasi KTP"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if($total_pages > 1): ?>
            <div class="mt-12 flex justify-center gap-2">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="w-10 h-10 flex items-center justify-center rounded-lg font-bold border <?= ($page == $i) ? 'bg-emerald-500 border-emerald-500 text-slate-900' : 'bg-slate-800 border-slate-700 text-slate-400 hover:text-white' ?> transition-all">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>