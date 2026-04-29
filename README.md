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
Gunakan akun berikut untuk mencoba fitur di setiap role.

| Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | `Admin_Tavern` | admin123 |
| **Penjual** | `ProGamer_ID` | 234 |
| **Pembeli** | `GuestBuyer_99` | 1234 |

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

## 📖 Tutorial Lengkap & Panduan Akses Per Role

Berikut adalah panduan langkah demi langkah untuk menggunakan fitur-fitur utama di TavernEx berdasarkan masing-masing role. Sistem kini telah diperbarui dengan fitur notifikasi *real-time* dan sistem penyimpanan gambar berbasis *filesystem* yang lebih optimal.

### 1. 🛒 Role: Pembeli (The Adventurer)
Pembeli dapat mencari dan membeli akun atau item game dengan jaminan keamanan penuh melalui sistem Rekber (Escrow).
- **Cara Akses**:
  1. Login menggunakan akun pembeli (`GuestBuyer_99`).
  2. Anda akan langsung diarahkan ke halaman utama (Marketplace).
  3. Untuk melihat pesanan dan transaksi Anda, klik **Ikon Profil (pojok kanan atas)** > Pilih **"Pesanan Saya"** atau **"Dashboard"**.
- **Panduan Transaksi**:
  - **Eksplorasi Produk**: Gunakan kolom pencarian atau filter kategori (Mobile/PC) di halaman utama.
  - **Checkout (Beli)**: Klik produk, baca deskripsi dengan teliti, lalu klik tombol "Beli".
  - **Pembayaran Escrow**: Lakukan transfer ke rekening sistem (simulasi) dan tunggu Admin memverifikasi dana tersebut.
  - **Pantau Pesanan**: Cek menu "Pesanan Saya". Jika statusnya "Processing", penjual sedang memproses pesanan Anda.
  - **Terima Akun/Item**: Setelah penjual mengirimkan pesanan, buka detail pesanan. Anda akan melihat kredensial login (username/password) dan bukti pengiriman dari penjual.
  - **Selesaikan**: Amankan akun Anda, lalu klik "Selesaikan Pesanan" agar dana bisa diteruskan ke penjual. Berikan ulasan/rating untuk reputasi penjual.

### 2. 💰 Role: Penjual (The Merchant)
Penjual dapat mengelola katalog produk, toko, dan memproses pesanan masuk.
- **Cara Akses (PENTING)**:
  1. Login menggunakan akun penjual (`ProGamer_ID`).
  2. Untuk masuk ke area manajemen toko, **wajib** klik **Ikon Profil (pojok kanan atas)** > Pilih **"Seller Dashboard"** (atau Kelola Toko/Pesanan). *Menu khusus penjual hanya bisa diakses melalui dropdown profil ini.*
- **Panduan Pengelolaan Toko**:
  - **Buka Toko**: Atur profil toko Anda, termasuk jam operasional dan status (Buka/Tutup).
  - **Posting Produk**: Masuk ke menu produk dan tambahkan item baru. Isi detail lengkap, harga, stok, dan unggah gambar produk (sistem kini mendukung upload gambar yang lebih cepat via direktori lokal).
  - **Proses Pesanan Masuk**: Pantau ikon Lonceng Notifikasi. Jika ada pesanan berstatus "Processing" (dana sudah diamankan Admin), segera proses pesanan tersebut.
  - **Kirim Data**: Masukkan kredensial login akun (Email/Password) di kolom enkripsi yang tersedia dan unggah bukti pengiriman (screenshot).
  - **Pencairan Saldo**: Dana akan otomatis masuk ke dompet akun Anda setelah pembeli mengkonfirmasi pesanan selesai.

### 3. 🛡️ Role: Admin (The Overseer)
Admin memiliki kontrol penuh atas lalu lintas transaksi dan moderasi platform.
- **Cara Akses**:
  1. Login menggunakan akun admin (`Admin_Tavern`).
  2. Klik **Ikon Profil (pojok kanan atas)** > Pilih **"Admin Dashboard"**.
- **Panduan Moderasi & Sistem**:
  - **Verifikasi Dana (Tugas Utama)**: Buka menu "Transaksi" atau "Escrow". Cari transaksi yang berstatus "Pending". Jika dana valid, klik **"Verifikasi Dana"** agar penjual bisa mulai mengirim pesanan.
  - **Manajemen Pengguna**: Verifikasi pendaftaran toko baru (validasi KTP/Identitas).
  - **Moderasi Platform**: Blokir/tutup toko yang terindikasi penipuan dan hapus postingan produk yang melanggar aturan.
  - **Penyelesaian Sengketa**: Menjadi penengah jika ada komplain dari pembeli terkait data akun yang tidak valid.

---

## 🛠️ Update Sistem Terbaru
Sistem TavernEx telah menerima beberapa peningkatan arsitektur untuk performa dan keamanan:
- **Migrasi Penyimpanan Gambar**: Beralih dari penyimpanan database *Base64* ke *Filesystem-based architecture* lokal. Mempercepat waktu muat halaman katalog secara signifikan dan mengurangi beban *query* database.
- **Sistem Notifikasi Menyeluruh**: Implementasi notifikasi *site-wide* untuk memberitahu penjual (ada pesanan baru) dan pembeli (status pesanan berubah) tanpa harus me-refresh halaman terus-menerus.
- **Perbaikan UI/UX Escrow**: Alur serah terima data login kini lebih rapi tanpa *layout bugs*, menjamin data sensitif hanya terlihat di sisi pembeli yang berhak.

---

## 💻 Teknologi yang Digunakan
- **Frontend**: HTML5, Vanilla CSS, Tailwind CSS (via CDN), JavaScript.
- **Backend**: PHP 8.x Native (Non-Framework).
- **Database**: MySQL (Optimized Queries).
- **UI Components**: Phosphor Icons, Google Fonts (Inter).

---
*TavernEx - Solusi Rekber Gaming Terpercaya. Dibuat dengan ❤️ untuk komunitas gamer.*
