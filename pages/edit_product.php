<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product data
$stmt = $conn->prepare("SELECT * FROM products WHERE id=? AND seller_id=?");
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$product = stmt_fetch_assoc($stmt);

if(!$product) {
    header("Location: seller_products.php");
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
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Find category name
    $game_name = "";
    foreach($categories as $cat) {
        if($cat['id'] == $category_id) {
            $game_name = $cat['name'];
            break;
        }
    }
    
    $image_url = null;
    if(!empty($_FILES['product_image']['name'])) {
        $foto = $_FILES['product_image']['name'];
        $target_dir = "../assets/images/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
        $foto_baru = time() . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $foto_baru;
        
        if(move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)){
            $image_url = "assets/images/" . $foto_baru;
            
            // Delete old file if it was a file path
            if(!empty($product['image_url']) && strpos($product['image_url'], 'data:') !== 0) {
                $old_file = "../" . $product['image_url'];
                if(file_exists($old_file)) { unlink($old_file); }
            }
        }
    }
    
    if ($image_url) {
        $upd = $conn->prepare("UPDATE products SET category_id=?, title=?, price=?, stock=?, game=?, description=?, login_type=?, product_type=?, is_active=?, image_url=? WHERE id=? AND seller_id=?");
        $upd->bind_param("isiissssisii", $category_id, $title, $price, $stock, $game_name, $description, $login_type, $product_type, $is_active, $image_url, $product_id, $seller_id);
    } else {
        $upd = $conn->prepare("UPDATE products SET category_id=?, title=?, price=?, stock=?, game=?, description=?, login_type=?, product_type=?, is_active=? WHERE id=? AND seller_id=?");
        $upd->bind_param("isiissssiii", $category_id, $title, $price, $stock, $game_name, $description, $login_type, $product_type, $is_active, $product_id, $seller_id);
    }
    
    if($upd && $upd->execute()) {
        $success = "Produk berhasil diperbarui!";
        // Refresh product data
        $stmt->execute();
        $product = stmt_fetch_assoc($stmt);
    } else {
        $error = "Gagal memperbarui produk: " . ($upd ? $upd->error : $conn->error);
    }
}

require_once '../includes/header.php'; 
?>

<div class="max-w-4xl mx-auto px-4 py-8 w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Produk</h1>
            <p class="text-slate-400 text-sm">Sesuaikan detail produk Anda.</p>
        </div>
        <a href="seller_products.php" class="text-slate-400 hover:text-white flex items-center gap-2">
            <i class="ph ph-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if(isset($success)): ?>
        <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl mb-6 text-sm border border-emerald-500/30 flex items-center justify-between">
            <div>
                <i class="ph-fill ph-check-circle mr-1"></i> <?= $success ?>
            </div>
            <a href="seller_products.php" class="text-white hover:text-emerald-300 font-bold underline">Lihat Semua Dagangan</a>
        </div>
    <?php endif; ?>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 p-6 md:p-8 shadow-xl">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Judul Produk</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Kategori Game</label>
                    <div class="relative">
                        <select name="category_id" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 pl-4 pr-10 text-white focus:border-emerald-500 focus:outline-none appearance-none" required>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= $cat['name'] ?></option>
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
                            <option value="Akun" <?= $product['product_type'] == 'Akun' ? 'selected' : '' ?>>Akun Game</option>
                            <option value="Item" <?= $product['product_type'] == 'Item' ? 'selected' : '' ?>>Item</option>
                            <option value="Gamepass" <?= $product['product_type'] == 'Gamepass' ? 'selected' : '' ?>>Gamepass</option>
                            <option value="Joki" <?= $product['product_type'] == 'Joki' ? 'selected' : '' ?>>Jasa Joki</option>
                            <option value="Mata Uang" <?= $product['product_type'] == 'Mata Uang' ? 'selected' : '' ?>>Mata Uang / Top Up</option>
                        </select>
                        <i class="ph ph-caret-down absolute right-4 top-3.5 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Foto Produk (Thumbnail)</label>
                    <div class="flex items-center gap-4">
                        <?php if($product['image_url']): ?>
                            <img src="<?= strpos($product['image_url'], 'http') === 0 || strpos($product['image_url'], 'data:') === 0 ? $product['image_url'] : '../' . $product['image_url'] ?>" class="w-12 h-12 rounded-lg object-cover border border-slate-600">
                        <?php endif; ?>
                        <input type="file" name="product_image" class="flex-grow bg-slate-900 border border-slate-600 rounded-lg py-2.5 px-4 text-white text-sm focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Harga (Rupiah)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold">Rp</span>
                        <input type="number" name="price" value="<?= $product['price'] ?>" min="1000" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 pl-12 pr-4 text-white focus:border-emerald-500 focus:outline-none" required>
                    </div>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Tipe Login</label>
                    <input type="text" name="login_type" value="<?= htmlspecialchars($product['login_type']) ?>" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-2">Stok Produk</label>
                    <input type="number" name="stock" value="<?= $product['stock'] ?>" min="-1" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none" required>
                    <p class="text-[10px] text-slate-500 mt-1">*Isi -1 untuk stok tidak terbatas (unlimited)</p>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-slate-300 text-sm font-semibold mb-2">Deskripsi Produk</label>
                <textarea name="description" rows="5" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-3 px-4 text-white focus:border-emerald-500 focus:outline-none resize-none" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="mb-8 flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" <?= $product['is_active'] ? 'checked' : '' ?> class="w-5 h-5 accent-emerald-500">
                <label for="is_active" class="text-slate-300 text-sm font-semibold">Tampilkan produk ini di katalog (Aktif)</label>
            </div>

            <button type="submit" class="w-full md:w-auto md:px-10 bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                <i class="ph-fill ph-floppy-disk"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
