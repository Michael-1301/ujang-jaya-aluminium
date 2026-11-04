@extends('layouts.app')

@section('title', 'Produk Aluminium')

@section('content')

<div class="hero">
    <span class="hero-eyebrow">Sejak dipercaya oleh ratusan pelanggan</span>
    <h1>Aluminium Berkualitas untuk Setiap Kebutuhan Bangunan Anda</h1>
    <p>Pintu, jendela, partisi, hingga kanopi aluminium custom dengan bahan pilihan dan pengerjaan rapi. Pesan langsung dari toko online kami.</p>

    <div class="hero-stats">
        <div class="hero-stat">
            <strong id="heroTotalProduk">-</strong>
            <span>Produk Tersedia</span>
        </div>
        <div class="hero-stat">
            <strong>100%</strong>
            <span>Bahan Aluminium Asli</span>
        </div>
        <div class="hero-stat">
            <strong>QRIS</strong>
            <span>Pembayaran Instan</span>
        </div>
    </div>
</div>

<div class="search-wrapper">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
    <input type="text" id="search" class="search-box" placeholder="Cari produk aluminium, misal: pintu, jendela, kanopi...">
</div>

<div class="section-heading">
    <h2>Produk Pilihan</h2>
</div>

<div class="grid" id="produkGrid"></div>

<div id="emptyState" class="empty-state-box" style="display:none;">
    <div class="empty-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
    </div>
    <h3>Produk tidak ditemukan</h3>
    <p>Coba gunakan kata kunci lain untuk pencarian Anda.</p>
</div>

@endsection

@push('scripts')
<script>
const grid = document.getElementById("produkGrid");
const searchInput = document.getElementById("search");
let allProducts = [];

// ambil data dari Firestore
db.collection("product").orderBy("created_at", "desc")
.onSnapshot(snapshot => {
    allProducts = [];
    snapshot.forEach(doc => {
        allProducts.push({
            id: doc.id,
            ...doc.data()
        });
    });
    document.getElementById("heroTotalProduk").textContent = allProducts.length;
    renderProduk(allProducts);
});

function renderProduk(data) {
    grid.innerHTML = "";

    document.getElementById("emptyState").style.display = data.length === 0 ? "block" : "none";

    data.forEach(p => {
        grid.innerHTML += `
            <div class="card">
                <div class="card-media">
                    <img src="${p.gambar}" alt="${p.nama}" loading="lazy">
                </div>
                <div class="card-body">
                    <h3>${p.nama}</h3>
                    <p class="price">Rp ${Number(p.harga).toLocaleString('id-ID')}</p>
                    <a href="/produk/${p.id}" class="btn-detail">
                        Lihat Detail
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        `;
    });
}

// search
searchInput.addEventListener("keyup", function () {
    const keyword = this.value.toLowerCase();
    const filtered = allProducts.filter(p =>
        p.nama.toLowerCase().includes(keyword)
    );
    renderProduk(filtered);
});
</script>
@endpush
