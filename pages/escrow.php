<?php 
require_once '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$trx_id = $_GET['id'] ?? '';

// Ambil Data Transaksi & Produk
$stmt = $conn->prepare("SELECT t.*, p.title, p.price, p.color_theme, s.username as seller_name, b.username as buyer_name 
                        FROM transactions t 
                        JOIN products p ON t.product_id = p.id 
                        JOIN users s ON p.seller_id = s.id 
                        JOIN users b ON t.buyer_id = b.id 
                        WHERE t.id = ?");
$stmt->bind_param("s", $trx_id);
$stmt->execute();
$trx = $stmt->get_result()->fetch_assoc();

if(!$trx) { echo "Transaksi tidak ditemukan"; exit; }

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Chat messages are now handled via AJAX through chat_api.php
// to prevent screen blinking during message sending.


// Ambil semua pesan chat
$chat_query = $conn->query("SELECT * FROM chat_messages WHERE transaction_id = '$trx_id' ORDER BY created_at ASC");
$messages = [];
while($row = $chat_query->fetch_assoc()) {
    $messages[] = $row;
}

require_once '../includes/header.php'; 
?>

<div class="h-full w-full max-w-7xl mx-auto flex flex-col lg:flex-row overflow-hidden border-x border-slate-800 flex-grow">
            
    <div class="w-full lg:w-1/3 bg-slate-850 border-r border-slate-700 flex flex-col h-full overflow-y-auto">
        <div class="p-5 border-b border-slate-700 bg-slate-800 sticky top-0 z-10">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="ph-fill ph-handshake text-emerald-500"></i> Detail Transaksi
            </h2>
            <p class="text-xs text-slate-400 mt-1">ID: #<?= $trx['id'] ?></p>
        </div>
        
        <div class="p-5 flex flex-col gap-6">
            <div class="bg-slate-900 rounded-xl p-4 border border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300 mb-4">Status Escrow</h3>
                <div class="relative pl-6 border-l-2 border-slate-700 space-y-6">
                    <div class="relative">
                        <div class="absolute -left-[33px] top-0.5 h-4 w-4 rounded-full bg-emerald-500 border-4 border-slate-900"></div>
                        <p class="text-sm font-bold text-emerald-400">Menunggu Pembayaran</p>
                        <p class="text-xs text-slate-500 mt-1">Pembeli transfer ke sistem.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[33px] top-0.5 h-4 w-4 rounded-full border-4 border-slate-900 <?= ($trx['status'] == 'processing' || $trx['status'] == 'completed') ? 'bg-emerald-500' : 'bg-slate-600' ?>"></div>
                        <p class="text-sm font-bold <?= ($trx['status'] == 'processing' || $trx['status'] == 'completed') ? 'text-emerald-400' : 'text-slate-400' ?>">Proses Serah Terima</p>
                        <p class="text-xs text-slate-500 mt-1">Penjual mengirim data login.</p>
                    </div>
                    <div class="relative">
                        <div class="absolute -left-[33px] top-0.5 h-4 w-4 rounded-full border-4 border-slate-900 <?= ($trx['status'] == 'completed') ? 'bg-emerald-500' : 'bg-slate-600' ?>"></div>
                        <p class="text-sm font-bold <?= ($trx['status'] == 'completed') ? 'text-emerald-400' : 'text-slate-400' ?>">Selesai & Dana Cair</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-300 mb-3">Item yang dibeli</h3>
                <div class="flex gap-3 bg-slate-900 p-3 rounded-xl border border-slate-700">
                    <div class="h-16 w-16 rounded-lg flex-shrink-0 <?= $trx['color_theme'] ?>"></div>
                    <div class="flex flex-col justify-between">
                        <p class="text-sm font-bold text-white line-clamp-2"><?= $trx['title'] ?></p>
                        <p class="text-emerald-400 font-bold"><?= formatRupiah($trx['price']) ?></p>
                    </div>
                </div>
            </div>

                <?php if($role == 'seller' && $trx['status'] == 'processing' && empty($trx['delivery_proof'])): ?>
                    <button onclick="document.getElementById('deliveryModal').classList.remove('hidden')" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-slate-900 rounded-lg font-bold text-sm shadow-lg">
                        <i class="ph-fill ph-paper-plane-tilt"></i> Kirim Data & Bukti Pesanan
                    </button>
                <?php endif; ?>

                <?php if($role == 'admin'): ?>
                    <?php if($trx['status'] == 'pending'): ?>
                        <button onclick="handleAction('verify_payment')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm">
                            <i class="ph ph-check-square"></i> Verifikasi Dana Masuk
                        </button>
                    <?php elseif($trx['status'] == 'processing'): ?>
                        <?php if(!empty($trx['delivery_proof'])): ?>
                            <div class="bg-amber-500/10 border border-amber-500/30 p-3 rounded-lg mb-3">
                                <p class="text-xs text-amber-500 mb-2 font-bold">Penjual sudah kirim bukti!</p>
                                <a href="../<?= $trx['delivery_proof'] ?>" target="_blank" class="text-[10px] text-blue-400 underline">Klik Lihat Bukti Pengiriman</a>
                            </div>
                            <button onclick="handleAction('finish_transaction')" class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-slate-900 rounded-lg font-bold text-sm shadow-lg">
                                <i class="ph-fill ph-check-circle"></i> Verifikasi & Selesaikan
                            </button>
                        <?php else: ?>
                            <div class="bg-slate-800 border border-slate-700 p-3 rounded-lg text-center">
                                <p class="text-xs text-slate-500 italic">Menunggu penjual mengirimkan pesanan...</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if($trx['status'] == 'completed'): ?>
                    <?php if($role == 'buyer' && !empty($trx['sensitive_data'])): ?>
                        <div class="bg-emerald-500/10 border border-emerald-500/30 p-4 rounded-xl mb-3">
                            <p class="text-xs text-emerald-500 font-bold mb-2 uppercase tracking-wider">Data Login Akun:</p>
                            <div class="bg-slate-900 p-3 rounded-lg border border-slate-700 font-mono text-sm text-white whitespace-pre-wrap select-all"><?= htmlspecialchars($trx['sensitive_data']) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="w-full py-3 bg-slate-800 text-emerald-400 text-center rounded-lg font-bold border border-emerald-500/30">
                        <i class="ph-fill ph-seal-check"></i> Transaksi Selesai
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-2/3 bg-slate-900 flex flex-col h-[calc(100vh-64px)] relative">
        <div class="h-16 border-b border-slate-700 bg-slate-800 flex items-center justify-between px-5 flex-shrink-0">
            <div class="flex items-center gap-3">
                <a href="../index.php" class="lg:hidden text-slate-400 hover:text-white"><i class="ph ph-arrow-left text-xl"></i></a>
                <div>
                    <h3 class="font-bold text-white leading-none">Ruang Escrow Tertutup</h3>
                    <p class="text-[10px] text-emerald-400 mt-1 flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> 3 Orang (End-to-End Encrypted)</p>
                </div>
            </div>
        </div>

        <div class="flex-grow overflow-y-auto p-5 space-y-4 chat-scroll bg-[#0b1120]" id="chatContainer">
            <div class="flex items-center justify-center h-full">
                <i class="ph-bold ph-circle-notch animate-spin text-4xl text-slate-700"></i>
            </div>
        </div>

        <div class="p-4 bg-slate-800 border-t border-slate-700 flex items-center gap-3">
            <form id="chatForm" class="w-full flex items-center gap-3">
                <input type="hidden" name="action" value="send_message">
                <input type="text" id="messageInput" name="message" autocomplete="off" <?= ($trx['status'] == 'completed') ? 'disabled' : 'required' ?>
                       class="flex-grow bg-slate-900 border border-slate-700 text-white rounded-full px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 disabled:opacity-50"
                       placeholder="<?= ($trx['status'] == 'completed') ? 'Transaksi telah selesai' : 'Ketik pesan di sini...' ?>">
                <button type="submit" <?= ($trx['status'] == 'completed') ? 'disabled' : '' ?>
                        class="p-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-900 rounded-full transition-colors disabled:opacity-50">
                    <i class="ph-fill ph-paper-plane-right text-xl"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Kirim Pesanan (Seller Only) -->
<div id="deliveryModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex justify-center items-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-2"><i class="ph-fill ph-paper-plane-tilt text-emerald-500"></i> Kirim Pesanan</h3>
            <button onclick="document.getElementById('deliveryModal').classList.add('hidden')" class="text-slate-400 hover:text-white"><i class="ph ph-x text-2xl"></i></button>
        </div>
        
        <form id="deliveryForm" onsubmit="event.preventDefault(); handleDeliverySubmit();">
            <div class="mb-4">
                <label class="block text-slate-400 text-xs font-bold mb-2 uppercase">Bukti Pengiriman (Screenshot)</label>
                <div class="relative group">
                    <input type="file" id="delivery_image" class="w-full bg-slate-900 border border-slate-600 rounded-lg py-2 px-4 text-white text-sm focus:outline-none focus:border-emerald-500" required>
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-slate-400 text-xs font-bold mb-2 uppercase">Data Login / Informasi Produk (Opsional)</label>
                <textarea id="sensitive_data" rows="4" class="w-full bg-slate-900 border border-slate-600 rounded-lg p-3 text-white text-sm focus:outline-none focus:border-emerald-500" placeholder="Email: example@mail.com&#10;Password: *****"></textarea>
                <p class="text-[10px] text-slate-500 mt-2 italic">*Data ini hanya akan bisa dilihat oleh PEMBELI setelah admin memverifikasi pengiriman Anda.</p>
            </div>
            
            <button type="submit" id="deliverySubmitBtn" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-bold py-3 rounded-xl transition-all shadow-lg shadow-emerald-500/20 flex justify-center items-center gap-2">
                Kirim Sekarang
            </button>
        </form>
    </div>
</div>

<script>
    const chatContainer = document.getElementById("chatContainer");
    const chatForm = document.getElementById("chatForm");
    const messageInput = document.getElementById("messageInput");
    const trxId = "<?= $trx_id ?>";
    const currentRole = "<?= $role ?>";

    function scrollToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    async function fetchMessages() {
        try {
            const res = await fetch(`chat_api.php?action=get_messages&trx_id=${trxId}`);
            const data = await res.json();
            if(data.status === 'success') {
                renderMessages(data.data);
            }
        } catch (e) { console.error(e); }
    }

    function renderMessages(messages) {
        // Keep track of current message count to avoid unnecessary re-renders if no new messages
        // Simple implementation: replace innerHTML if different
        let html = '';
        messages.forEach(msg => {
            const isMe = (msg.sender_role === currentRole);
            const roleColor = getRoleColor(msg.sender_role);
            
            if(msg.sender_role === 'system') {
                html += `
                    <div class="flex justify-center my-2">
                        <span class="bg-slate-800 text-slate-400 text-[11px] font-mono px-3 py-1.5 rounded border border-slate-700 whitespace-pre-wrap text-center max-w-[80%]">${msg.message}</span>
                    </div>`;
            } else {
                html += `
                    <div class="flex w-full ${isMe ? 'justify-end' : 'justify-start'}">
                        <div class="flex max-w-[80%] flex-col ${isMe ? 'items-end' : 'items-start'}">
                            ${!isMe ? `<span class="text-[10px] font-bold mb-1 ml-1 ${roleColor}">${msg.sender_name} (${msg.sender_role})</span>` : ''}
                            <div class="p-3 relative group ${isMe ? 'bg-emerald-600 text-white rounded-2xl rounded-br-none' : (msg.sender_role=='admin' ? 'bg-slate-700 text-white border border-slate-600 rounded-2xl rounded-bl-none' : 'bg-slate-800 text-slate-200 border border-slate-700 rounded-2xl rounded-bl-none')}">
                                <p class="text-sm whitespace-pre-wrap leading-relaxed">${msg.message}</p>
                            </div>
                            <span class="text-[9px] text-slate-500 mt-1 mr-1">${msg.time}</span>
                        </div>
                    </div>`;
            }
        });
        
        const isAtBottom = chatContainer.scrollHeight - chatContainer.scrollTop <= chatContainer.clientHeight + 100;
        
        if(chatContainer.innerHTML !== html) {
            chatContainer.innerHTML = html;
            if(isAtBottom) scrollToBottom();
        }
    }

    function getRoleColor(role) {
        if(role === 'admin') return 'text-amber-500';
        if(role === 'seller') return 'text-emerald-500';
        return 'text-blue-400';
    }

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const msg = messageInput.value.trim();
        if(!msg) return;

        messageInput.value = '';
        try {
            const res = await fetch('chat_api.php?action=send_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ trx_id: trxId, message: msg })
            });
            const data = await res.json();
            if(data.status === 'success') {
                fetchMessages();
            }
        } catch (e) { console.error(e); }
    });

    async function handleDeliverySubmit() {
        const fileInput = document.getElementById('delivery_image');
        const sensitive = document.getElementById('sensitive_data').value;
        const btn = document.getElementById('deliverySubmitBtn');
        
        if(fileInput.files.length === 0) {
            alert('Silakan pilih foto bukti pengiriman.');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Mengirim...';

        const formData = new FormData();
        formData.append('trx_id', trxId);
        formData.append('delivery_image', fileInput.files[0]);
        formData.append('sensitive_data', sensitive);

        try {
            const res = await fetch('chat_api.php?action=submit_delivery', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if(data.status === 'success') {
                window.location.reload();
            } else {
                alert(data.msg);
                btn.disabled = false;
                btn.innerHTML = 'Kirim Sekarang';
            }
        } catch (e) { 
            console.error(e);
            btn.disabled = false;
            btn.innerHTML = 'Kirim Sekarang';
        }
    }

    async function handleAction(type) {
        if(!confirm(`Apakah Anda yakin ingin melakukan tindakan ini?`)) return;
        
        try {
            const res = await fetch('chat_api.php?action=handle_action', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ trx_id: trxId, type: type })
            });
            const data = await res.json();
            if(data.status === 'success') {
                // Refresh page to update the status tracking UI and buttons
                // To avoid blink on message sending, we used AJAX. 
                // For status changes, a single refresh is often better to reset all state.
                // But the user said "setiap kali mengirim pesan".
                window.location.reload();
            } else {
                alert(data.msg);
            }
        } catch (e) { console.error(e); }
    }

    // Auto refresh every 3 seconds
    setInterval(fetchMessages, 3000);
    window.onload = () => {
        fetchMessages();
        setTimeout(scrollToBottom, 500);
    };
</script>

<?php require_once '../includes/footer.php'; ?>