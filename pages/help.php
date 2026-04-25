<?php 
require_once '../config/db.php';
require_once '../includes/header.php'; 
?>

<div class="max-w-4xl mx-auto px-4 py-12 w-full flex-grow">
    <div class="text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-4">Pusat Bantuan TavernEx</h1>
        <p class="text-slate-400 text-lg">Temukan jawaban untuk pertanyaan Anda seputar transaksi dan layanan kami.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
        <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-emerald-500/30 transition shadow-xl">
            <div class="bg-emerald-500/20 w-12 h-12 rounded-xl flex items-center justify-center text-emerald-500 mb-4">
                <i class="ph-fill ph-shield-check text-2xl"></i>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Keamanan Escrow</h3>
            <p class="text-slate-400 text-sm leading-relaxed">Dana Anda ditahan oleh sistem TavernEx hingga Anda mengonfirmasi bahwa data game telah diterima dan sesuai dengan deskripsi.</p>
        </div>
        <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-blue-500/30 transition shadow-xl">
            <div class="bg-blue-500/20 w-12 h-12 rounded-xl flex items-center justify-center text-blue-500 mb-4">
                <i class="ph-fill ph-identification-card text-2xl"></i>
            </div>
            <h3 class="text-white font-bold text-lg mb-2">Verifikasi Penjual</h3>
            <p class="text-slate-400 text-sm leading-relaxed">Penjual dengan tanda centang hijau telah memverifikasi identitas mereka dengan KTP, memberikan lapisan keamanan tambahan bagi pembeli.</p>
        </div>
    </div>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden shadow-xl mb-12">
        <div class="p-6 border-b border-slate-700 bg-slate-850">
            <h2 class="text-xl font-bold text-white">Pertanyaan Sering Diajukan (FAQ)</h2>
        </div>
        <div class="divide-y divide-slate-700">
            <div class="p-6">
                <h4 class="text-white font-bold mb-2">Berapa lama proses escrow berlangsung?</h4>
                <p class="text-slate-400 text-sm">Biasanya 5-30 menit tergantung respon penjual. Jika penjual tidak merespon dalam 24 jam, pesanan dapat dibatalkan otomatis.</p>
            </div>
            <div class="p-6">
                <h4 class="text-white font-bold mb-2">Bagaimana jika akun yang saya beli terkena hackback?</h4>
                <p class="text-slate-400 text-sm">TavernEx memberikan garansi perlindungan. Segera laporkan ke admin melalui fitur "Laporkan Produk" atau hubungi CS kami dengan bukti yang kuat.</p>
            </div>
            <div class="p-6">
                <h4 class="text-white font-bold mb-2">Bagaimana cara menjadi penjual terverifikasi?</h4>
                <p class="text-slate-400 text-sm">Anda dapat mengajukan verifikasi di menu Profil dengan mengunggah foto KTP dan nomor WhatsApp aktif.</p>
            </div>
        </div>
    </div>

    <div class="bg-emerald-500 rounded-2xl p-8 text-center shadow-lg shadow-emerald-500/20">
        <h3 class="text-slate-900 font-extrabold text-2xl mb-2">Masih Butuh Bantuan?</h3>
        <p class="text-emerald-900 font-medium mb-6">Tim dukungan kami siap membantu Anda 24/7 untuk memastikan transaksi Anda lancar.</p>
        <a href="https://wa.me/628123456789" target="_blank" class="inline-flex items-center gap-2 bg-slate-900 text-white font-bold py-3 px-8 rounded-xl hover:bg-slate-800 transition">
            <i class="ph-fill ph-whatsapp"></i> Hubungi WhatsApp CS
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
