<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Check verification status
$stmt = $conn->prepare("SELECT is_verified FROM users WHERE id=?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user['is_verified']) {
    header("Location: verification.php");
    exit;
}

// Fetch categories
$cats_stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while($cat = $cats_stmt->fetch_assoc()) {
    $categories[] = $cat;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $price = (int)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);
    $login_type = trim($_POST['login_type']);
    $product_type = $_POST['product_type'];
    $stock = (int)$_POST['stock'];
    
    $image_url = NULL;
    if(!empty($_FILES['product_image']['name'])) {
        $target_dir = "../uploads/products/";
        $file_ext = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . $seller_id . "." . $file_ext;
        $target_file = $target_dir . $file_name;
        
        if(move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_url = "uploads/products/" . $file_name;
        }
    }
    
    // Find category name for the 'game' column (legacy sync)
    $game_name = "";
    foreach($categories as $cat) {
        if($cat['id'] == $category_id) {
            $game_name = $cat['name'];
            break;
        }
    }
    
    // Pick random color theme for variety
    $themes = [
        'bg-gradient-to-br from-blue-900 to-indigo-800',
        'bg-gradient-to-br from-red-900 to-rose-800',
        'bg-gradient-to-br from-emerald-900 to-teal-800',
        'bg-gradient-to-br from-amber-900 to-orange-800',
        'bg-gradient-to-br from-purple-900 to-fuchsia-800'
    ];
    $color_theme = $themes[array_rand($themes)];
    
    $ins = $conn->prepare("INSERT INTO products (seller_id, category_id, title, price, stock, game, description, login_type, product_type, image_url, color_theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->bind_param("iiiisssssss", $seller_id, $category_id, $title, $price, $stock, $game_name, $description, $login_type, $product_type, $image_url, $color_theme);
    
    if($ins->execute()) {
        $success = "Produk berhasil ditambahkan!";
    } else {
        $error = "Gagal menambahkan produk.";
    }
}

require_once '../includes/header.php'; 
?>

<div class="max-w-4xl mx-auto px-4 py-8 w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Tambah Produk Baru</h1>
            <p class="text-slate-400 text-sm">Pastikan detail akun valid dan sesuai dengan deskripsi.</p>
        </div>
        <div class="bg-emerald-500/10 text-emerald-400 px-3 py-1.5 rounded-full border border-emerald-500/30 text-xs font-bold flex items-center gap-1.5">
            <i class="ph-fill ph-seal-check"></i> Seller Verificated
        </div>
    </div>

    <?php if(isset($success)): ?>
        <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl mb-6 text-sm border border-emerald-500/30 flex items-center justify-between">
            <div>
                <i class="ph-fill ph-check-circle mr-1"></i> <?= $success ?>
            </div>
            <a href="../index.php" class="text-white hover:text-emerald-300 font-bold underline">Lihat Katalog</a>
        </div>
    <?php endif; ?>
    <?php if(isset($error)): ?>
        <div class="bg-red-500/20 text-red-400 p-4 rounded-xl mb-6 text-sm border border-red-500/30">
            <i class="ph-fill ph-warning-circle mr-1"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 md:p-8 shadow-xl">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Judul Produk</label>
                    <input type="text" name="title" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required placeholder="Contoh: Akun Valorant Rank Immortal + Skin">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Kategori Game</label>
                    <div class="relative">
                        <select name="category_id" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 pl-4 pr-10 text-white focus:border-emerald-500 focus:outline-none appearance-none" required>
                            <option value="">-- Pilih Game --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-3.5 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Tipe Produk</label>
                    <div class="relative">
                        <select name="product_type" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 pl-4 pr-10 text-white focus:border-emerald-500 focus:outline-none appearance-none" required>
                            <option value="Akun">Akun Game</option>
                            <option value="Item">Item</option>
                            <option value="Gamepass">Gamepass</option>
                            <option value="Joki">Jasa Joki</option>
                            <option value="Mata Uang">Mata Uang / Top Up</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-3.5 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Foto Produk (Thumbnail)</label>
                    <input type="file" name="product_image" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-2.5 px-4 text-white text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Harga (Rupiah)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold">Rp</span>
                        <input type="number" name="price" min="1000" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 pl-12 pr-4 text-white focus:border-emerald-500 focus:outline-none" required placeholder="150000">
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Tipe Login</label>
                    <input type="text" name="login_type" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required placeholder="Contoh: Riot ID, Moonton, Email">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Stok Produk</label>
                    <input type="number" name="stock" min="-1" value="1" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required>
                    <p class="text-[10px] text-slate-500 mt-1">*Isi -1 untuk stok tidak terbatas (unlimited)</p>
                </div>
            </div>

            <div class="mb-8">
                <label class="block text-slate-300 text-sm font-semibold mb-2">Deskripsi Produk</label>
                <textarea name="description" rows="5" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none resize-none" required placeholder="Jelaskan spesifikasi akun secara detail..."></textarea>
                <p class="text-xs text-slate-500 mt-2">Dilarang menyertakan nomor HP atau link pihak ketiga di dalam deskripsi barang.</p>
            </div>

            <button type="submit" class="w-full md:w-auto md:px-10 bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                <i class="ph-fill ph-plus-circle"></i> Terbitkan Produk
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
