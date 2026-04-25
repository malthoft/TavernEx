// store.js - Simulasi Backend Supabase Sementara
const Store = {
    getUser() {
        return JSON.parse(localStorage.getItem('tavernex_user'));
    },
    setUser(user) {
        localStorage.setItem('tavernex_user', JSON.stringify(user));
    },
    logout() {
        localStorage.removeItem('tavernex_user');
        window.location.href = 'index.html';
    },
    getActiveTransaction() {
        return JSON.parse(localStorage.getItem('tavernex_trx'));
    },
    setActiveTransaction(trx) {
        localStorage.setItem('tavernex_trx', JSON.stringify(trx));
    },
    // Data Katalog Statis
    products: [
        { id: 101, title: "Akun Genshin Impact AR 60", price: 1500000, game: "Genshin Impact", seller: "ProGamer_ID", verifiedSeller: true, vip: true, colorTheme: "bg-gradient-to-br from-blue-900 to-indigo-800" },
        { id: 102, title: "Valorant Rank Immortal 2", price: 2800000, game: "Valorant", seller: "AimeeStore", verifiedSeller: true, vip: false, colorTheme: "bg-gradient-to-br from-red-900 to-rose-800" }
    ]
};