@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan toko dan produk Anda')

@section('page-actions')
    <a href="/admin/produk" class="btn-primary">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Produk
    </a>
@endsection

@section('content')

<!-- STAT CARDS -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statTotalProduk">0</span>
            <span class="stat-label">Total Produk</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statTotalPesanan">0</span>
            <span class="stat-label">Total Pesanan</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statPendapatan">Rp 0</span>
            <span class="stat-label">Pendapatan Terkonfirmasi</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon sky">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statMenunggu">0</span>
            <span class="stat-label">Menunggu Verifikasi</span>
        </div>
    </div>
</div>

<div class="section-heading">
    <h2>Daftar Produk</h2>
</div>

<div class="admin-toolbar">
    <div class="admin-search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="searchProduk" placeholder="Cari produk berdasarkan nama...">
    </div>
</div>

<div id="listProduk" class="produk-grid"></div>

<div id="emptyProduk" class="empty-state-box" style="display:none;">
    <div class="empty-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
    </div>
    <h3>Belum ada produk</h3>
    <p>Klik "Tambah Produk" untuk mulai menambahkan produk aluminium Anda.</p>
</div>

@endsection


@push('scripts')
<script>
let allProdukAdmin = [];

/* =====================
   daftar produk saya
===================== */
db.collection("product")
.orderBy("created_at", "desc")
.onSnapshot(snapshot => {

    allProdukAdmin = [];
    snapshot.forEach(doc => {
        allProdukAdmin.push({ id: doc.id, ...doc.data() });
    });

    document.getElementById("statTotalProduk").textContent = allProdukAdmin.length;
    renderProdukAdmin(allProdukAdmin);
});

function renderProdukAdmin(data){
    let html = "";

    data.forEach(p => {
        html += `
        <div class="produk-card">

            <img src="${p.gambar || ''}">

            <div class="produk-info">
                <h4>${p.nama}</h4>

                <p class="harga">
                    Rp ${Number(p.harga || 0).toLocaleString('id-ID')}
                </p>

                <div class="aksi-btn">
                    <a href="/admin/produk/edit/${p.id}" class="edit-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        Edit
                    </a>
                    <button onclick="hapusProduk('${p.id}')" class="hapus-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Hapus
                    </button>
                </div>
            </div>

        </div>
        `;
    });

    document.getElementById("listProduk").innerHTML = html;
    document.getElementById("emptyProduk").style.display = data.length === 0 ? "block" : "none";
}

document.getElementById("searchProduk").addEventListener("keyup", function(){
    const keyword = this.value.toLowerCase();
    const filtered = allProdukAdmin.filter(p => (p.nama || "").toLowerCase().includes(keyword));
    renderProdukAdmin(filtered);
});


/* =====================
   hapus produk
===================== */
function hapusProduk(id){
    if(confirm("Hapus produk ini?")){
        db.collection("product").doc(id).delete();
    }
}


/* =====================
   STAT: PESANAN & PENDAPATAN
===================== */
db.collection("orders").onSnapshot(snapshot => {
    let total = 0;
    let pendapatan = 0;
    let menunggu = 0;

    snapshot.forEach(doc => {
        const o = doc.data();
        total++;
        if (o.payment_status === "paid") {
            pendapatan += Number(o.harga || 0);
        }
        if (o.payment_status === "waiting_confirmation") {
            menunggu++;
        }
    });

    document.getElementById("statTotalPesanan").textContent = total;
    document.getElementById("statMenunggu").textContent = menunggu;
    document.getElementById("statPendapatan").textContent =
        "Rp " + pendapatan.toLocaleString('id-ID');
});
</script>
@endpush
