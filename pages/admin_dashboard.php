<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle Admin Actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['approve_seller'])) {
        $req_id = $_POST['req_id'];
        $seller_id = $_POST['seller_id'];
        $conn->query("UPDATE verification_requests SET status='approved' WHERE id='$req_id'");
        $conn->query("UPDATE users SET is_verified=1, is_seller=1 WHERE id='$seller_id'");
        $msg = "Seller berhasil diverifikasi.";
    }
    if(isset($_POST['reject_seller'])) {
        $req_id = $_POST['req_id'];
        $conn->query("UPDATE verification_requests SET status='rejected' WHERE id='$req_id'");
        $msg = "Pengajuan seller ditolak.";
    }
    if(isset($_POST['resolve_report'])) {
        $report_id = $_POST['report_id'];
        $conn->query("UPDATE reports SET status='resolved' WHERE id='$report_id'");
        $msg = "Laporan berhasil ditandai selesai.";
    }
    if(isset($_POST['add_category'])) {
        $cat_name = trim($_POST['category_name']);
        if(!empty($cat_name)) {
            $slug = strtolower(str_replace(' ', '-', $cat_name));
            $image_url = NULL;
            
            if(!empty($_FILES['category_image']['name'])) {
                $foto = $_FILES['category_image']['name'];
                $target_dir = "../assets/images/";
                if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
                $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                $foto_baru = time() . "_" . uniqid() . "." . $ext;
                $target_file = $target_dir . $foto_baru;
                if(move_uploaded_file($_FILES['category_image']['tmp_name'], $target_file)){
                    $image_url = "assets/images/" . $foto_baru;
                }
            }
            
            $device_type = $_POST['device_type'] ?? 'both';
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, image_url, device_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $cat_name, $slug, $image_url, $device_type);
            if($stmt->execute()) {
                $msg = "Kategori game baru berhasil ditambahkan.";
            } else {
                $error = "Kategori gagal ditambahkan (mungkin sudah ada).";
            }
        }
    }
    if(isset($_POST['edit_category'])) {
        $cat_id = $_POST['cat_id'];
        $cat_name = trim($_POST['category_name']);
        $slug = strtolower(str_replace(' ', '-', $cat_name));
        
        $image_url = null;
        if(!empty($_FILES['category_image']['name'])) {
            $foto = $_FILES['category_image']['name'];
            $target_dir = "../assets/images/";
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
            $foto_baru = time() . "_" . uniqid() . "." . $ext;
            $target_file = $target_dir . $foto_baru;
            if(move_uploaded_file($_FILES['category_image']['tmp_name'], $target_file)){
                $image_url = "assets/images/" . $foto_baru;
                
                // Get old image
                $stmt_old = $conn->prepare("SELECT image_url FROM categories WHERE id=?");
                $stmt_old->bind_param("i", $cat_id);
                $stmt_old->execute();
                $old_row = stmt_fetch_assoc($stmt_old);
                if($old_row && !empty($old_row['image_url']) && strpos($old_row['image_url'], 'data:') !== 0) {
                    $old_file = "../" . $old_row['image_url'];
                    if(file_exists($old_file)) { unlink($old_file); }
                }
            }
        }
        
        $device_type = $_POST['device_type'] ?? 'both';
        
        if ($image_url) {
            $upd = $conn->prepare("UPDATE categories SET name=?, slug=?, device_type=?, image_url=? WHERE id=?");
            $upd->bind_param("ssssi", $cat_name, $slug, $device_type, $image_url, $cat_id);
            $upd->execute();
        } else {
            $upd = $conn->prepare("UPDATE categories SET name=?, slug=?, device_type=? WHERE id=?");
            $upd->bind_param("sssi", $cat_name, $slug, $device_type, $cat_id);
            $upd->execute();
        }
        $msg = "Kategori berhasil diperbarui.";
    }
    if(isset($_POST['delete_category'])) {
        $cat_id = $_POST['cat_id'];
        
        $stmt_old = $conn->prepare("SELECT image_url FROM categories WHERE id=?");
        $stmt_old->bind_param("i", $cat_id);
        $stmt_old->execute();
        $old_row = stmt_fetch_assoc($stmt_old);
        if($old_row && !empty($old_row['image_url']) && strpos($old_row['image_url'], 'data:') !== 0) {
            $old_file = "../" . $old_row['image_url'];
            if(file_exists($old_file)) { unlink($old_file); }
        }
        
        $conn->query("DELETE FROM categories WHERE id='$cat_id'");
        $msg = "Kategori berhasil dihapus.";
    }
}

// Queries for dashboard data
$pending_verifications = $conn->query("SELECT v.*, u.username FROM verification_requests v JOIN users u ON v.seller_id=u.id WHERE v.status='pending'");
$pending_reports = $conn->query("SELECT r.*, p.title, p.id as product_id, u.username as reporter_name FROM reports r JOIN products p ON r.product_id=p.id JOIN users u ON r.reporter_id=u.id WHERE r.status='pending'");
$active_transactions = $conn->query("SELECT t.*, p.title, b.username as buyer_name, s.username as seller_name FROM transactions t JOIN products p ON t.product_id=p.id JOIN users b ON t.buyer_id=b.id JOIN users s ON p.seller_id=s.id WHERE t.status != 'completed'");
$categories_list = $conn->query("SELECT * FROM categories ORDER BY name ASC");

require_once '../includes/header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 py-8 w-full flex-grow flex flex-col md:flex-row gap-8">
    <!-- Sidebar Admin -->
    <div class="w-full md:w-1/4">
        <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden sticky top-24">
            <div class="p-6 border-b border-slate-700 bg-slate-850">
                <div class="flex items-center gap-3 mb-1">
                    <i class="ph-fill ph-shield-star text-3xl text-amber-500"></i>
                    <h2 class="text-xl font-bold text-white">Admin Panel</h2>
                </div>
                <p class="text-xs text-slate-400">TavernEx Escrow Center</p>
            </div>
            <div class="p-4 flex flex-col gap-2">
                <a href="#verifikasi" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-500/10 text-emerald-400 font-semibold border border-emerald-500/20">
                    <i class="ph-fill ph-identification-card text-lg"></i> Verifikasi KTP
                    <?php if($pending_verifications->num_rows > 0): ?>
                        <span class="ml-auto bg-emerald-500 text-slate-900 text-[10px] px-2 py-0.5 rounded-full"><?= $pending_verifications->num_rows ?></span>
                    <?php endif; ?>
                </a>
                <a href="#laporan" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                    <i class="ph-fill ph-flag text-lg"></i> Laporan Produk
                    <?php if($pending_reports->num_rows > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full"><?= $pending_reports->num_rows ?></span>
                    <?php endif; ?>
                </a>
                <a href="#transaksi" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                    <i class="ph-fill ph-handshake text-lg"></i> Transaksi Aktif
                </a>
                <a href="#kategori" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                    <i class="ph-fill ph-game-controller text-lg"></i> Kategori Game
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full md:w-3/4 space-y-8">
        <?php if(isset($msg)): ?>
            <div class="bg-emerald-500/20 text-emerald-400 p-4 rounded-xl border border-emerald-500/30 font-semibold flex items-center gap-2">
                <i class="ph-fill ph-check-circle"></i> <?= $msg ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="bg-red-500/20 text-red-400 p-4 rounded-xl border border-red-500/30 font-semibold flex items-center gap-2">
                <i class="ph-fill ph-warning-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Section Verifikasi KTP -->
        <section id="verifikasi" class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="ph-fill ph-identification-card text-emerald-500"></i> Antrean Verifikasi Penjual</h3>
            </div>
            <div class="p-6">
                <?php if($pending_verifications->num_rows == 0): ?>
                    <p class="text-slate-400 text-sm text-center py-4">Semua pengajuan sudah diproses.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php while($v = $pending_verifications->fetch_assoc()): ?>
                            <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-12 bg-slate-800 border border-slate-600 rounded flex items-center justify-center relative">
                                        <i class="ph-fill ph-identification-card text-slate-400 text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-white font-bold text-sm"><?= $v['username'] ?> <span class="text-xs text-slate-400 font-normal">(<?= htmlspecialchars($v['full_name']) ?>)</span></p>
                                        <p class="text-slate-400 text-xs mt-1"><i class="ph-fill ph-identification-card text-emerald-500"></i> NIK: <?= htmlspecialchars($v['nik_ktp']) ?></p>
                                        <p class="text-slate-400 text-xs mt-1"><i class="ph-fill ph-whatsapp text-emerald-500"></i> <?= htmlspecialchars($v['whatsapp_number']) ?></p>
                                    </div>
                                </div>
                                <div class="flex gap-2 w-full md:w-auto">
                                    <form method="POST" action="" class="flex-grow">
                                        <input type="hidden" name="req_id" value="<?= $v['id'] ?>">
                                        <button name="reject_seller" class="w-full px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg text-sm font-semibold transition border border-red-500/20">Tolak</button>
                                    </form>
                                    <form method="POST" action="" class="flex-grow">
                                        <input type="hidden" name="req_id" value="<?= $v['id'] ?>">
                                        <input type="hidden" name="seller_id" value="<?= $v['seller_id'] ?>">
                                        <button name="approve_seller" class="w-full px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-900 rounded-lg text-sm font-bold transition">Setujui</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section Laporan Produk -->
        <section id="laporan" class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="ph-fill ph-flag text-red-500"></i> Laporan Produk</h3>
            </div>
            <div class="p-6">
                <?php if($pending_reports->num_rows == 0): ?>
                    <p class="text-slate-400 text-sm text-center py-4">Tidak ada laporan saat ini.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php while($r = $pending_reports->fetch_assoc()): ?>
                            <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 flex flex-col items-start gap-3">
                                <div class="flex items-center justify-between w-full">
                                    <h4 class="text-white font-bold text-sm flex items-center gap-2"><i class="ph-fill ph-warning-circle text-red-500"></i> Dilaporkan oleh: <?= $r['reporter_name'] ?></h4>
                                    <a href="product.php?id=<?= $r['product_id'] ?>" class="text-xs text-blue-400 hover:underline" target="_blank">Lihat Produk <i class="ph ph-arrow-up-right"></i></a>
                                </div>
                                <div class="bg-red-500/10 border border-red-500/20 p-3 rounded-lg w-full">
                                    <p class="text-slate-300 text-sm">"<?= htmlspecialchars($r['reason']) ?>"</p>
                                </div>
                                <form method="POST" action="" class="w-full mt-2">
                                    <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                    <button name="resolve_report" class="w-full px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-semibold transition border border-slate-600">Tandai Selesai ditinjau</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section Transaksi Aktif -->
        <section id="transaksi" class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="ph-fill ph-handshake text-blue-400"></i> Pantauan Transaksi Aktif</h3>
            </div>
            <div class="p-6">
                <?php if($active_transactions->num_rows == 0): ?>
                    <p class="text-slate-400 text-sm text-center py-4">Belum ada transaksi aktif yang berjalan.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-slate-400 text-xs uppercase border-b border-slate-700 bg-slate-900/50">
                                    <th class="px-4 py-3 font-semibold">Tansaksi ID</th>
                                    <th class="px-4 py-3 font-semibold">Produk</th>
                                    <th class="px-4 py-3 font-semibold">Buyer / Seller</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php while($t = $active_transactions->fetch_assoc()): ?>
                                    <tr class="border-b border-slate-700 hover:bg-slate-750">
                                        <td class="px-4 py-4 text-white font-mono"><?= $t['id'] ?></td>
                                        <td class="px-4 py-4 text-slate-300 font-medium whitespace-nowrap overflow-hidden text-ellipsis max-w-[150px]"><?= $t['title'] ?></td>
                                        <td class="px-4 py-4 text-slate-300">
                                            <span class="text-blue-400"><?= $t['buyer_name'] ?></span> / <span class="text-emerald-400"><?= $t['seller_name'] ?></span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <?php if($t['status'] == 'pending'): ?>
                                                <span class="bg-slate-700 text-slate-300 px-2.5 py-1 text-xs rounded-full">Pending TF</span>
                                            <?php else: ?>
                                                <span class="bg-orange-500/20 text-orange-400 border border-orange-500/30 px-2.5 py-1 text-xs rounded-full inline-flex items-center gap-1.5"><i class="ph-fill ph-hourglass-high animate-spin-slow"></i> Processing</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <a href="escrow.php?id=<?= $t['id'] ?>" class="inline-block bg-slate-700 hover:bg-slate-600 border border-slate-600 text-white px-3 py-1.5 rounded text-sm transition font-medium">Masuk Escrow</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section Kelola Kategori -->
        <section id="kategori" class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-lg">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="ph-fill ph-game-controller text-purple-400"></i> Kelola Kategori Game</h3>
            </div>
            <div class="p-6">
                <form method="POST" action="" class="mb-8 bg-slate-900/50 p-4 rounded-xl border border-slate-700" enctype="multipart/form-data">
                    <h4 class="text-white font-bold text-sm mb-4">Tambah Kategori Baru</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-slate-400 text-xs font-bold mb-1 uppercase">Nama Game</label>
                            <input type="text" name="category_name" placeholder="Contoh: Genshin Impact" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 px-4 text-white focus:outline-none focus:border-emerald-500" required>
                        </div>
                        <div>
                            <label class="block text-slate-400 text-xs font-bold mb-1 uppercase">Tipe Device</label>
                            <select name="device_type" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 px-4 text-white focus:outline-none focus:border-emerald-500">
                                <option value="mobile">Mobile Only</option>
                                <option value="pc">PC Only</option>
                                <option value="both" selected>Keduanya (Mobile & PC)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-slate-400 text-xs font-bold mb-1 uppercase">Gambar Kategori</label>
                            <input type="file" name="category_image" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-1.5 px-4 text-white text-sm focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <button type="submit" name="add_category" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-2 px-6 rounded-lg transition-colors">Simpan Kategori</button>
                </form>
                
                <h4 class="text-sm text-slate-400 mb-3">Daftar Kategori Terdaftar:</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while($cat = $categories_list->fetch_assoc()): ?>
                        <div class="bg-slate-900 border border-slate-700 rounded-xl p-4 flex flex-col gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex-shrink-0">
                                    <?php if($cat['image_url']): ?>
                                        <img src="<?= strpos($cat['image_url'], 'http') === 0 || strpos($cat['image_url'], 'data:') === 0 ? $cat['image_url'] : '../' . $cat['image_url'] ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-600"><i class="ph ph-image text-2xl"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <p class="text-white font-bold text-sm truncate"><?= $cat['name'] ?></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <p class="text-slate-500 text-[10px] truncate">/<?= $cat['slug'] ?></p>
                                        <span class="text-[8px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 font-bold uppercase border border-slate-700"><?= $cat['device_type'] ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="openEditCat('<?= $cat['id'] ?>', '<?= htmlspecialchars($cat['name']) ?>', '<?= $cat['device_type'] ?>')" class="flex-grow bg-slate-800 hover:bg-slate-700 text-slate-300 py-1.5 rounded-lg text-xs font-bold border border-slate-700 transition">Edit</button>
                                <form method="POST" class="inline" onsubmit="return confirm('Hapus kategori ini? Semua produk terkait mungkin akan bermasalah.')">
                                    <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" name="delete_category" class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 rounded-lg text-xs font-bold border border-red-500/20 transition">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <!-- Modal Edit Kategori -->
        <div id="edit-cat-modal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex justify-center items-center p-4">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Edit Kategori</h3>
                    <button onclick="document.getElementById('edit-cat-modal').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="cat_id" id="edit_cat_id">
                    <div class="mb-4">
                        <label class="block text-slate-400 text-xs font-bold mb-1 uppercase">Nama Game</label>
                        <input type="text" name="category_name" id="edit_cat_name" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-2.5 px-4 text-white focus:outline-none focus:border-emerald-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-slate-400 text-xs font-bold mb-1 uppercase">Tipe Device</label>
                        <select name="device_type" id="edit_cat_device" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-2.5 px-4 text-white focus:outline-none focus:border-emerald-500">
                            <option value="mobile">Mobile Only</option>
                            <option value="pc">PC Only</option>
                            <option value="both">Keduanya (Mobile & PC)</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-slate-400 text-xs font-bold mb-1 uppercase">Ganti Gambar (Opsional)</label>
                        <input type="file" name="category_image" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-2 px-4 text-white text-sm focus:outline-none focus:border-emerald-500">
                    </div>
                    <button type="submit" name="edit_category" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl transition-all shadow-lg shadow-emerald-500/20">Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <script>
            function openEditCat(id, name, device) {
                document.getElementById('edit_cat_id').value = id;
                document.getElementById('edit_cat_name').value = name;
                document.getElementById('edit_cat_device').value = device;
                document.getElementById('edit-cat-modal').classList.remove('hidden');
            }
        </script>

    </div>
</div>

<script>
    // Simple smooth scrolling for sidebar links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
