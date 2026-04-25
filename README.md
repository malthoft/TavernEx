# TavernEx - Premium Gaming Marketplace 🛡️🎮

**TavernEx** adalah platform marketplace gaming modern yang dirancang untuk transaksi akun, item, dan jasa joki yang aman, transparan, dan profesional. Dengan inspirasi dari platform terkemuka seperti Itemku, TavernEx menghadirkan sistem **Escrow (Rekber)** terintegrasi untuk menjamin keamanan dana pembeli dan kepastian pembayaran bagi penjual.

---

## 🔗 Link Demo
Nikmati pengalaman penuh TavernEx di:  
👉 **[althof.site/tavernex](http://althof.site/tavernex)**

---

## 🎯 Tujuan & Maksud Program
Tujuan utama TavernEx adalah menyediakan "Tavern" atau tempat persinggahan yang aman bagi para gamer untuk bertransaksi aset digital. Masalah utama dalam jual-beli akun game adalah risiko *hack-back* dan penipuan. TavernEx memitigasi hal ini dengan:
1.  **Sistem Penengah (Admin/Midman)**: Dana tidak langsung ke penjual, melainkan ditahan sistem.
2.  **Verifikasi Berjenjang**: Penjual wajib verifikasi KTP untuk membangun kepercayaan.
3.  **Transparansi Data**: Detail produk yang terstruktur (tipe login, device, deskripsi).

---

## 🔑 Akun Uji Coba (Credentials)
Gunakan akun berikut untuk mencoba fitur di setiap role. Password untuk semua akun di bawah adalah: `password123`

| Role | Username | Kegunaan |
| :--- | :--- | :--- |
| **Admin** | `Admin_Tavern` | Verifikasi transaksi, verifikasi penjual, & moderasi konten. |
| **Penjual** | `ProGamer_ID` | Menjual akun/item, kirim pesanan, & cek riwayat dagangan. |
| **Pembeli** | `GuestBuyer_99` | Mencari produk, beli via escrow, & ulasan produk. |

---

## 🧪 Pengujian Kualitas (Berdasarkan Use Case Diagram)

Pengujian dilakukan untuk memastikan semua alur dalam *Use Case Diagram* berfungsi dengan standar kualitas tinggi.

| Fitur / Aspek | Skenario Uji | Status | Hasil yang Diharapkan |
| :--- | :--- | :--- | :--- |
| **Inisiasi Transaksi** | Pembeli melakukan checkout produk. | ✅ Berhasil | Transaksi masuk ke sistem dengan status 'Pending'. |
| **Verifikasi Pembayaran** | Admin memverifikasi dana yang dikirim pembeli. | ✅ Berhasil | Status berubah menjadi 'Processing', penjual mendapat notifikasi. |
| **Serah Terima Data** | Penjual mengunggah bukti & data login akun. | ✅ Berhasil | Data sensitif tersimpan dan tersembunyi hingga verifikasi admin. |
| **Keamanan Data** | Admin mengecek bukti kirim tanpa melihat password. | ✅ Berhasil | Password hanya tampil di sisi pembeli setelah transaksi 'Completed'. |
| **Cari & Filter** | Mencari game berdasarkan kategori & device (Mobile/PC). | ✅ Berhasil | Hasil pencarian akurat dan responsif. |
| **Verifikasi Akun** | Admin memvalidasi pendaftaran penjual baru. | ✅ Berhasil | Role user berubah menjadi seller dan bisa memposting produk. |
| **Laporan (Flagging)** | Pembeli melaporkan postingan mencurigakan. | ✅ Berhasil | Laporan masuk ke dashboard admin untuk ditindaklanjuti. |
| **Manajemen Stok** | Membeli produk dengan stok terbatas. | ✅ Berhasil | Stok otomatis berkurang dan sold count bertambah. |

---

## 📖 Panduan Penggunaan Per Role

### 1. 🛡️ Role: Admin (The Overseer)
Admin bertanggung jawab menjaga ekosistem TavernEx tetap sehat dan aman.
- **Akses Dashboard**: Mengelola statistik platform.
- **Verifikasi Dana**: Cek menu transaksi, pastikan pembeli sudah transfer, lalu klik "Verifikasi Dana".
- **Verifikasi Penjual**: Cek data KTP/identitas pendaftar penjual dan setujui jika valid.
- **Moderasi**: Menutup toko yang melanggar aturan atau menghapus produk ilegal.

### 2. 💰 Role: Penjual (The Merchant)
Penjual bisa berdagang setelah akun diverifikasi oleh admin.
- **Buka Toko**: Atur jam operasional dan status toko (Buka/Tutup).
- **Posting Produk**: Masukkan detail game, tipe login (Moonton/Riot/dll), stok, dan harga.
- **Proses Pesanan**: Jika ada pembeli, tunggu dana diverifikasi admin. Setelah itu, unggah bukti screenshot pengiriman dan tulis data login akun di kolom yang tersedia.
- **Tarik Saldo**: Pantau saldo yang masuk dari pesanan yang sukses.

### 3. 🛒 Role: Pembeli (The Adventurer)
Pembeli mencari kenyamanan dan keamanan saat mencari aset game.
- **Eksplorasi**: Gunakan fitur pencarian dan filter game populer di homepage.
- **Beli via Escrow**: Lakukan checkout, transfer ke rekening sistem (simulasi), dan tunggu admin verifikasi.
- **Ambil Data**: Setelah penjual mengirim data dan admin menyetujui, pembeli dapat melihat data login di halaman Escrow.
- **Ulasan**: Berikan rating dan komen setelah transaksi selesai untuk reputasi penjual.

---

## 💻 Teknologi yang Digunakan
- **Frontend**: HTML5, Vanilla CSS, Tailwind CSS (via CDN), JavaScript (AJAX/Fetch).
- **Backend**: PHP 8.x Native.
- **Database**: MySQL.
- **UI Components**: Phosphor Icons, Google Fonts (Inter).

---
*TavernEx - Solusi Rekber Gaming Terpercaya. Dibuat dengan ❤️ untuk komunitas gamer.*
