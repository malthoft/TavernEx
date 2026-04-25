<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TavernEx - Secure Gaming Marketplace</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: { slate: { 850: '#151e2e' } }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #e2e8f0; }
        .chat-scroll::-webkit-scrollbar { width: 6px; }
        .chat-scroll::-webkit-scrollbar-track { background: #1e293b; }
        .chat-scroll::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        .chat-scroll::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col relative overflow-x-hidden">

<?php
$logo_link = "/tavernex/index.php";
if(isset($_SESSION['role'])) {
    if($_SESSION['role'] === 'admin') $logo_link = "/tavernex/pages/admin_dashboard.php";
    elseif($_SESSION['role'] === 'seller') $logo_link = "/tavernex/pages/seller_dashboard.php";
}
?>

<nav class="bg-slate-800 border-b border-slate-700 sticky top-0 z-40 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-4 md:gap-8">
                <a href="<?= $logo_link ?>" class="flex items-center gap-2 cursor-pointer">
                    <div class="bg-emerald-500 p-1.5 rounded-lg text-slate-900">
                        <i class="ph-fill ph-shield-check text-xl md:text-2xl"></i>
                    </div>
                    <span class="font-bold text-lg md:text-xl tracking-tight text-white">Tavern<span class="text-emerald-500">Ex</span></span>
                </a>
                
                <div class="hidden md:flex items-center gap-6 border-l border-slate-700 pl-8">
                    <?php if(!isset($_SESSION['role']) || $_SESSION['role'] === 'buyer'): ?>
                        <a href="/tavernex/index.php?view=catalog" class="text-slate-300 hover:text-white font-medium text-sm transition-colors">
                            Katalog Produk
                        </a>
                    <?php elseif($_SESSION['role'] === 'seller'): ?>
                        <a href="/tavernex/pages/seller_products.php" class="text-slate-300 hover:text-white font-medium text-sm transition-colors">
                            Kelola Dagangan
                        </a>
                        <a href="/tavernex/pages/seller_orders.php" class="text-slate-300 hover:text-white font-medium text-sm transition-colors">
                            Pesanan Masuk
                        </a>
                    <?php elseif($_SESSION['role'] === 'admin'): ?>
                        <a href="/tavernex/pages/admin_dashboard.php" class="text-slate-300 hover:text-white font-medium text-sm transition-colors">
                            Dashboard Admin
                        </a>
                    <?php endif; ?>
                    <a href="/tavernex/pages/help.php" class="text-slate-300 hover:text-white font-medium text-sm transition-colors">
                        Pusat Bantuan
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2 md:gap-4">
                <?php if(!isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-2 md:gap-4">
                        <a href="/tavernex/pages/login.php" class="text-slate-300 hover:text-white font-medium text-sm transition-colors px-2">Masuk</a>
                        <a href="/tavernex/pages/register.php" class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-1.5 px-3 md:px-5 rounded-full text-xs md:text-sm transition-all shadow-lg shadow-emerald-500/20">Daftar</a>
                    </div>
                <?php else: ?>
                    <?php 
                        $uid = $_SESSION['user_id'];
                        $notif_q = $conn->query("SELECT COUNT(*) as unread FROM notifications WHERE user_id='$uid' AND is_read=0");
                        $notif_count = $notif_q ? $notif_q->fetch_assoc()['unread'] : 0;
                        
                        $cart_count = 0;
                        if($_SESSION['role'] == 'buyer') {
                            $cart_q = $conn->query("SELECT COUNT(*) as count FROM cart WHERE buyer_id='$uid'");
                            $cart_count = $cart_q ? $cart_q->fetch_assoc()['count'] : 0;
                        }
                    ?>
                    <div class="flex items-center gap-3 md:gap-5 mr-2 md:mr-4 text-slate-400">
                        <a href="/tavernex/pages/help.php" class="md:hidden">
                            <i class="ph ph-question text-2xl hover:text-white transition"></i>
                        </a>
                        
                        <div class="relative group cursor-pointer">
                            <i class="ph-fill ph-bell text-2xl hover:text-white transition"></i>
                            <?php if($notif_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full font-bold shadow-lg shadow-red-500/50"><?= $notif_count ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if($_SESSION['role'] === 'buyer'): ?>
                            <a href="/tavernex/pages/wishlist.php" class="relative group cursor-pointer inline-block">
                                <i class="ph-fill ph-heart text-2xl hover:text-white transition cursor-pointer"></i>
                            </a>
                            <a href="/tavernex/pages/cart.php" class="relative group cursor-pointer inline-block">
                                <i class="ph-fill ph-shopping-cart text-2xl hover:text-white transition cursor-pointer"></i>
                                <?php if($cart_count > 0): ?>
                                    <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full font-bold shadow-lg shadow-orange-500/50"><?= $cart_count ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <a href="/tavernex/pages/profile.php" class="flex items-center gap-2 bg-slate-900 py-1.5 px-2 md:pr-5 rounded-full border border-slate-700 hover:border-emerald-500/50 transition">
                        <div class="h-8 w-8 rounded-full bg-slate-700 flex items-center justify-center text-emerald-500 font-bold shrink-0">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                        <div class="hidden md:flex flex-col">
                            <span class="text-xs font-bold text-white leading-none"><?= $_SESSION['username'] ?></span>
                            <span class="text-[10px] text-amber-500 uppercase tracking-wider font-bold mt-1"><?= $_SESSION['role'] ?></span>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="flex-grow overflow-y-auto flex flex-col">