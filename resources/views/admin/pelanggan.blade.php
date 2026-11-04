@extends('layouts.admin')

@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan & Performa Produk')
@section('page-subtitle', 'Lihat pelanggan berpotensi repeat order dan produk mana yang perlu didorong')

@section('content')

<!-- STAT CARDS -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statTotalPelanggan">0</span>
            <span class="stat-label">Total Pelanggan</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statRepeatPelanggan">0</span>
            <span class="stat-label">Berpotensi Repeat Order</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-meta">
            <span class="stat-value" id="statRataBelanja">Rp 0</span>
            <span class="stat-label">Rata-rata Belanja / Pelanggan</span>
        </div>
    </div>
</div>

<!-- ================= PELANGGAN ================= -->
<div class="section-heading">
    <h2>Daftar Pelanggan</h2>
</div>

<div class="admin-toolbar">
    <div class="admin-search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text" id="searchPelanggan" placeholder="Cari nama, no HP, atau email..." onkeyup="renderPelanggan()">
    </div>
    <select id="sortPelanggan" onchange="renderPelanggan()" style="padding:10px 14px;border-radius:10px;border:1px solid #e2e8f0;">
        <option value="jumlah_desc">Urutkan: Jumlah Pesanan Terbanyak</option>
        <option value="belanja_desc">Urutkan: Total Belanja Terbesar</option>
        <option value="terakhir_desc">Urutkan: Pesanan Terakhir</option>
    </select>
</div>

<div id="loadingPelanggan" class="loading-state">
    <div class="spinner"></div>
    <p>Memuat data pelanggan...</p>
</div>

<div class="admin-table-card" id="pelangganTableContainer" style="display:none;">
    <div class="table-responsive">
        <table id="pelangganTable">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Kontak</th>
                    <th>Jumlah Pesanan</th>
                    <th>Pesanan Selesai/Dibayar</th>
                    <th>Total Belanja</th>
                    <th>Pesanan Terakhir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="pelangganTableBody"></tbody>
        </table>
    </div>
</div>

<div id="emptyPelanggan" class="empty-state-box" style="display:none;">
    <h3>Belum Ada Pelanggan</h3>
    <p>Data pelanggan akan muncul setelah ada pesanan masuk.</p>
</div>

<!-- ================= PERFORMA PRODUK ================= -->
<div class="section-heading" style="margin-top:36px;">
    <h2>Performa Produk</h2>
</div>

<div class="grid-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <div class="admin-table-card">
        <div style="padding:16px 18px;border-bottom:1px solid #eef1f6;">
            <strong>🔥 Produk Terlaris</strong>
            <p style="margin:2px 0 0;color:#64748b;font-size:13px;">Berdasarkan jumlah pesanan masuk</p>
        </div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Produk</th><th>Jml Pesanan</th><th>Terjual (Dibayar)</th></tr></thead>
                <tbody id="produkLarisBody"></tbody>
            </table>
        </div>
    </div>

    <div class="admin-table-card">
        <div style="padding:16px 18px;border-bottom:1px solid #eef1f6;">
            <strong>📉 Perlu Didorong Penjualannya</strong>
            <p style="margin:2px 0 0;color:#64748b;font-size:13px;">Produk dengan pesanan paling sedikit / belum ada</p>
        </div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Produk</th><th>Jml Pesanan</th><th>Terjual (Dibayar)</th></tr></thead>
                <tbody id="produkSepiBody"></tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>

let allOrdersData = [];
let allProdukData = [];
let pelangganList = [];

/* =========================
   KEAMANAN
   Data pesanan ditulis oleh customer, jadi TIDAK boleh langsung
   dimasukkan ke innerHTML (cegah XSS tersimpan di halaman admin).
========================= */
function esc(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ubah nilai apa pun menjadi teks (data dari user bisa saja bukan teks)
function str(value) {
    return (value === undefined || value === null) ? '' : String(value);
}

async function loadPelangganData(){
    document.getElementById('loadingPelanggan').style.display = 'block';
    document.getElementById('pelangganTableContainer').style.display = 'none';
    document.getElementById('emptyPelanggan').style.display = 'none';

    try {
        const [ordersSnap, produkSnap] = await Promise.all([
            db.collection('orders').orderBy('created_at', 'desc').get(),
            db.collection('product').get()
        ]);

        allOrdersData = [];
        ordersSnap.forEach(doc => allOrdersData.push({ id: doc.id, ...doc.data() }));

        allProdukData = [];
        produkSnap.forEach(doc => allProdukData.push({ id: doc.id, ...doc.data() }));

        buildPelangganList();
        renderPelanggan();
        renderProdukPerforma();

    } catch (error) {
        console.error('Gagal memuat data pelanggan:', error);
        document.getElementById('loadingPelanggan').innerHTML =
            `<p style="color:#dc2626;">Gagal memuat data: ${esc(error.message)}</p>`;
    }
}

/* =========================
   GROUP ORDERS -> PER PELANGGAN
   (dikelompokkan by user_id, fallback ke no_hp jika kosong)
   Memakai Map, bukan objek biasa, supaya kunci seperti "__proto__"
   atau "constructor" dari data user tidak merusak perhitungan.
========================= */
function buildPelangganList(){
    const map = new Map();

    allOrdersData.forEach(o => {
        if (o.status === 'cancelled') return;

        const key = str(o.user_id || o.no_hp || o.user_email || o.nama_pemesan);
        if (!key) return;

        if (!map.has(key)) {
            map.set(key, {
                nama: str(o.nama_pemesan) || '-',
                no_hp: str(o.no_hp) || '-',
                email: str(o.user_email) || '-',
                jumlah: 0,
                jumlahDibayar: 0,
                totalBelanja: 0,
                terakhir: null
            });
        }

        const c = map.get(key);
        c.jumlah++;
        c.nama = str(o.nama_pemesan) || c.nama;
        c.no_hp = str(o.no_hp) || c.no_hp;

        if (o.payment_status === 'paid') {
            c.jumlahDibayar++;
            const harga = Number(o.harga);
            if (Number.isFinite(harga) && harga > 0) {
                c.totalBelanja += harga;
            }
        }

        const created = (o.created_at && typeof o.created_at.toDate === 'function')
            ? o.created_at.toDate()
            : null;
        if (created && (!c.terakhir || created > c.terakhir)) {
            c.terakhir = created;
        }
    });

    pelangganList = Array.from(map.values());
}

/* =========================
   RENDER TABEL PELANGGAN
========================= */
function renderPelanggan(){
    document.getElementById('loadingPelanggan').style.display = 'none';

    const search = document.getElementById('searchPelanggan').value.toLowerCase();
    const sortBy = document.getElementById('sortPelanggan').value;

    let list = pelangganList.filter(c =>
        c.nama.toLowerCase().includes(search) ||
        c.no_hp.toLowerCase().includes(search) ||
        c.email.toLowerCase().includes(search)
    );

    if (sortBy === 'jumlah_desc') list.sort((a,b) => b.jumlah - a.jumlah);
    if (sortBy === 'belanja_desc') list.sort((a,b) => b.totalBelanja - a.totalBelanja);
    if (sortBy === 'terakhir_desc') list.sort((a,b) => (b.terakhir||0) - (a.terakhir||0));

    document.getElementById('statTotalPelanggan').textContent = pelangganList.length;
    document.getElementById('statRepeatPelanggan').textContent = pelangganList.filter(c => c.jumlah >= 2).length;

    const totalBelanjaSemua = pelangganList.reduce((s,c) => s + c.totalBelanja, 0);
    const rata = pelangganList.length ? totalBelanjaSemua / pelangganList.length : 0;
    document.getElementById('statRataBelanja').textContent = formatRupiah(rata);

    if (list.length === 0) {
        document.getElementById('pelangganTableContainer').style.display = 'none';
        document.getElementById('emptyPelanggan').style.display = 'block';
        return;
    }

    document.getElementById('pelangganTableContainer').style.display = 'block';
    document.getElementById('emptyPelanggan').style.display = 'none';

    const tbody = document.getElementById('pelangganTableBody');
    tbody.innerHTML = list.map(c => {
        const badge = c.jumlah >= 3
            ? `<span class="status-badge status-paid">🔥 Pelanggan Setia</span>`
            : c.jumlah === 2
                ? `<span class="status-badge status-waiting">↻ Berpotensi Repeat</span>`
                : `<span class="status-badge status-pending">Pelanggan Baru</span>`;

        const tanggal = c.terakhir
            ? c.terakhir.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
            : '-';

        const emailHtml = (c.email && c.email !== '-')
            ? '<br><small style="color:#64748b;">' + esc(c.email) + '</small>'
            : '';

        return `
            <tr>
                <td><strong>${esc(c.nama)}</strong></td>
                <td>${esc(c.no_hp)}${emailHtml}</td>
                <td>${c.jumlah}</td>
                <td>${c.jumlahDibayar}</td>
                <td><strong>${formatRupiah(c.totalBelanja)}</strong></td>
                <td>${tanggal}</td>
                <td>${badge}</td>
            </tr>
        `;
    }).join('');
}

/* =========================
   PERFORMA PRODUK (laris vs kurang laris)
   Memakai Map supaya nama produk aneh ("__proto__", dll) tidak merusak data.
========================= */
function renderProdukPerforma(){
    const statMap = new Map();

    // mulai dari semua produk yang ada (termasuk yang belum pernah dipesan)
    allProdukData.forEach(p => {
        const nama = str(p.nama);
        if (nama) statMap.set(nama, { nama, jumlah: 0, dibayar: 0 });
    });

    allOrdersData.forEach(o => {
        if (o.status === 'cancelled') return;
        const nama = str(o.nama_produk) || 'Tanpa Nama';
        if (!statMap.has(nama)) statMap.set(nama, { nama, jumlah: 0, dibayar: 0 });
        const s = statMap.get(nama);
        s.jumlah++;
        if (o.payment_status === 'paid') s.dibayar++;
    });

    const semuaProduk = Array.from(statMap.values());

    const laris = [...semuaProduk].sort((a,b) => b.jumlah - a.jumlah).slice(0, 5);
    const sepi = [...semuaProduk].sort((a,b) => a.jumlah - b.jumlah).slice(0, 5);

    const row = p => `<tr><td>${esc(p.nama)}</td><td>${p.jumlah}</td><td>${p.dibayar}</td></tr>`;
    const kosong = `<tr><td colspan="3" style="text-align:center;color:#94a3b8;">Belum ada data</td></tr>`;

    document.getElementById('produkLarisBody').innerHTML = laris.length
        ? laris.map(row).join('')
        : kosong;

    document.getElementById('produkSepiBody').innerHTML = sepi.length
        ? sepi.map(row).join('')
        : kosong;
}

function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka || 0);
}

loadPelangganData();

</script>
@endpush