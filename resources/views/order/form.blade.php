@extends('layouts.app')

@section('title', 'Form Pesanan')

@section('content')

<div class="admin-page">
    <div class="admin-card">

        <h2>Form Pemesanan</h2>

        <form id="formOrder" class="admin-form">

            <label>Nama Pemesan</label>
            <input type="text"
                   id="nama_pemesan"
                   required
                   placeholder="Nama lengkap Anda"
                   oninvalid="this.setCustomValidity('Nama pemesan wajib diisi')"
                   oninput="this.setCustomValidity('')">

            <label>No HP</label>
            <input type="text"
                   id="no_hp"
                   required
                   pattern="^08[0-9]{8,11}$"
                   placeholder="contoh 08xxxxxxxxxx"
                   oninvalid="this.setCustomValidity('Nomor HP tidak valid')"
                   oninput="this.setCustomValidity('')">

            <label>Alamat Lengkap</label>
            <textarea id="alamat"
                      required
                      placeholder="Alamat pengiriman lengkap"
                      oninvalid="this.setCustomValidity('Alamat wajib diisi')"
                      oninput="this.setCustomValidity('')"></textarea>

            {{-- TOMBOL GPS --}}
            <button type="button"
                    class="btn-outline"
                    style="justify-content:center;width:100%;"
                    onclick="ambilLokasi()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                Gunakan Lokasi Anda Saat Ini
            </button>

            <label>Catatan (opsional)</label>
            <textarea id="catatan" placeholder="Catatan tambahan untuk pesanan Anda"></textarea>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    Lanjut ke Pembayaran
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
                <a href="/" class="btn-cancel">Batal</a>
            </div>

        </form>

    </div>
</div>

{{-- MODAL PEMBAYARAN SOLANA PAY --}}
<div id="qrisModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h2>💳 Pembayaran Solana Pay (Devnet)</h2>

        <div class="payment-info">
            <p><strong>Nama Produk:</strong> <span id="displayNamaProduk"></span></p>
            <p><strong>Total Bayar:</strong> <span id="displayHarga" style="font-size:20px; color:#2e7d32; font-weight:bold;"></span></p>
        </div>

        <div class="qris-container">
            <div id="qr-container" style="display:flex; justify-content:center;"></div>
        </div>

        <div class="upload-section" style="text-align:center;">
            <p id="payment-status" style="font-weight:bold; margin:0 0 6px;">⏳ Menunggu pembayaran...</p>
            <small style="color:#666;">Scan QR dengan aplikasi Phantom di HP (mode Devnet). Pesanan tersimpan otomatis setelah pembayaran terverifikasi di blockchain.</small>
        </div>

        <div class="modal-actions" style="margin-top:30px; display:flex; gap:10px; justify-content:center;">
            <button onclick="tutupModal()" class="btn-cancel">
                ❌ Batal
            </button>
        </div>
    </div>
</div>

@endsection


@push('styles')
<style>
/* Modal Overlay */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.payment-info {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0;
}

.payment-info p {
    margin: 8px 0;
}

.qris-container {
    text-align: center;
    margin: 20px 0;
}

.upload-section {
    background: #fff3e0;
    padding: 15px;
    border-radius: 8px;
    border: 2px dashed #ff9800;
}

.modal-actions button {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
}

.modal-actions .btn-cancel {
    background: var(--danger-500, #dc2626);
    color: white;
    text-decoration: none;
    display: inline-block;
}

.modal-actions .btn-cancel:hover {
    background: #b91c1c;
}

/* Modal Notifikasi Custom (pengganti alert() bawaan browser) */
.notif-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    z-index: 10002;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: notifFadeIn 0.15s ease-out;
}

@keyframes notifFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.notif-box {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 90%;
    max-width: 380px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
    text-align: center;
    animation: notifPopIn 0.18s ease-out;
}

@keyframes notifPopIn {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.notif-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
}

.notif-icon.notif-success { background: #dcfce7; color: #16a34a; }
.notif-icon.notif-warning { background: #fef3c7; color: #d97706; }
.notif-icon.notif-error   { background: #fee2e2; color: #dc2626; }

.notif-message {
    font-size: 14px;
    color: #334155;
    line-height: 1.5;
    margin-bottom: 22px;
}

.notif-box button {
    width: 100%;
    padding: 11px 16px;
    border-radius: 9px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    background: #1e3a8a;
    color: #fff;
    transition: filter 0.15s;
}

.notif-box button:hover {
    filter: brightness(0.92);
}
</style>
@endpush


@push('scripts')
{{-- Memuat bundle JS (Solana Pay) saja, tanpa CSS Tailwind, supaya tampilan web tidak berubah --}}
@vite(['resources/js/app.js'])

<script>

const produkId = "{{ $id }}";

/* =========================
   STATE GPS & ORDER DATA
========================= */
let userLat = null;
let userLng = null;
let currentProduk = null;
let currentOrderData = null;
let orderSaved = false;

/* =========================
   MODAL NOTIFIKASI CUSTOM
   Pengganti alert() bawaan browser, tampil di tengah & modern.
   type: 'success' | 'warning' | 'error'
========================= */
function showNotifModal(type, message) {
    const icons = {
        success: '✅',
        warning: '⚠️',
        error: '❌'
    };

    const overlay = document.createElement('div');
    overlay.className = 'notif-overlay';

    overlay.innerHTML = `
        <div class="notif-box">
            <div class="notif-icon notif-${type}">${icons[type] || 'ℹ️'}</div>
            <div class="notif-message">${message}</div>
            <button id="notifOkBtn">OK</button>
        </div>
    `;

    document.body.appendChild(overlay);

    const close = () => overlay.remove();
    overlay.querySelector('#notifOkBtn').onclick = close;
    overlay.onclick = (e) => { if (e.target === overlay) close(); };
}

/* =========================
   WAJIB LOGIN + KHUSUS CUSTOMER (bukan admin)
========================= */
let isAdminAccount = false;

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.replace("/login?from=order");
        return;
    }

    const snap = await db.collection("users").doc(user.uid).get();
    const role = snap.exists ? snap.data().role : "user";

    if (role === "admin") {
        isAdminAccount = true;
        alert("Akun admin tidak bisa membuat pesanan. Gunakan akun customer untuk memesan.");
        location.href = "/admin";
    }
});

/* =========================
   LOAD PRODUK DATA
========================= */
async function loadProdukData(){
    try {
        const produkDoc = await db.collection("product").doc(produkId).get();
        if(produkDoc.exists){
            currentProduk = produkDoc.data();
            console.log("Produk loaded:", currentProduk);
        } else {
            alert("Produk tidak ditemukan!");
            location.href = "/";
        }
    } catch(error){
        console.error("Error loading produk:", error);
        alert("Gagal memuat data produk");
    }
}

// Load produk saat halaman dibuka
loadProdukData();

/* =========================
   AMBIL LOKASI MENGGUNAKAN GPS
========================= */
function ambilLokasi(){
    if(!navigator.geolocation){
        showNotifModal('error', 'Browser tidak mendukung GPS');
        return;
    }

    // Show loading indicator
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = "⏳ Mengambil lokasi...";
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;

            try {
                // Reverse geocoding (gratis & aman)
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${userLat}&lon=${userLng}`,
                    {
                        headers: {
                            'User-Agent': 'OrderApp/1.0' // Required by Nominatim
                        }
                    }
                );
                const data = await res.json();

                if(data.display_name){
                    document.getElementById('alamat').value = data.display_name;
                    showNotifModal('success', 'Lokasi berhasil diambil!');
                } else {
                    showNotifModal('warning', 'Lokasi diambil, tapi alamat tidak ditemukan. Silakan isi manual.');
                }
            } catch(error){
                console.error("Reverse geocoding error:", error);
                showNotifModal('warning', 'Koordinat berhasil diambil, tapi gagal mendapatkan alamat. Silakan isi alamat manual.');
            }

            btn.innerHTML = "✅ Lokasi Valid";
            btn.style.background = "#2e7d32";
        },
        (error) => {
            console.error("Geolocation error:", error);
            showNotifModal('error', 'Gagal mengambil lokasi. Pastikan GPS aktif dan izin diberikan.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    );
}

/* =========================
   FORM SUBMIT - TAMPILKAN QR PEMBAYARAN
========================= */
document.getElementById("formOrder").addEventListener("submit", async (e)=>{
    e.preventDefault();

    // Validasi GPS
    if(!userLat || !userLng){
        alert("⚠️ Gunakan lokasi saat ini terlebih dahulu untuk verifikasi alamat.");
        return;
    }

    // Validasi user login
    const user = auth.currentUser;
    if(!user){
        location.href="/login";
        return;
    }

    if(isAdminAccount){
        alert("Akun admin tidak bisa membuat pesanan.");
        return;
    }

    // Validasi produk data
    if(!currentProduk){
        alert("❌ Data produk belum dimuat. Mohon refresh halaman.");
        return;
    }

    // Simpan data order sementara
    currentOrderData = {
        user_id: user.uid,
        user_email: user.email,
        produk_id: produkId,
        nama_produk: currentProduk.nama,
        harga: currentProduk.harga,
        nama_pemesan: document.getElementById('nama_pemesan').value,
        no_hp: document.getElementById('no_hp').value,
        alamat: document.getElementById('alamat').value,
        lat: userLat,
        lng: userLng,
        catatan: document.getElementById('catatan').value,
        status: "pending_payment",
        created_at: firebase.firestore.FieldValue.serverTimestamp()
    };

    // Tampilkan modal pembayaran
    tampilkanQRIS();
});

/* =========================
   TAMPILKAN QR SOLANA PAY
========================= */
function setStatus(text){
    document.getElementById('payment-status').textContent = text;
}

async function tampilkanQRIS(){
    // Pastikan bundle Solana Pay sudah termuat
    if(!window.SolanaPay){
        alert("❌ Modul pembayaran belum termuat. Refresh halaman dan coba lagi.");
        return;
    }

    orderSaved = false;
    const harga = Number(currentProduk.harga);

    document.getElementById('qrisModal').style.display = 'flex';
    document.getElementById('displayNamaProduk').textContent = currentProduk.nama;
    document.getElementById('displayHarga').textContent =
        formatRupiah(harga) + " (≈ " + window.SolanaPay.rupiahToSol(harga) + " SOL)";
    setStatus("⏳ Menunggu pembayaran...");

    try {
        await window.SolanaPay.start({
            amountRupiah: harga,
            orderRef: "ORD-" + Date.now(),
            containerId: "qr-container",
            onPaid: (pay) => simpanPesananSetelahBayar(pay)
        });
    } catch(error){
        console.error("Error membuat QR Solana Pay:", error);
        alert("❌ Gagal membuat QR pembayaran. Periksa harga produk dan coba lagi.");
        tutupModal(true);
    }
}

/* =========================
   SIMPAN PESANAN SETELAH PEMBAYARAN TERVERIFIKASI
========================= */
async function simpanPesananSetelahBayar(pay, percobaan = 1){
    if(orderSaved) return;
    orderSaved = true;
    setStatus("✅ Pembayaran terverifikasi di blockchain. Menyimpan pesanan...");

    try {
        const explorerUrl = `https://explorer.solana.com/tx/${pay.signature}?cluster=devnet`;

        currentOrderData.payment_method = "solana_pay";
        currentOrderData.payment_status = "waiting_confirmation"; // admin tetap yang menyetujui
        currentOrderData.payment_date = firebase.firestore.FieldValue.serverTimestamp();
        currentOrderData.tx_signature = pay.signature;
        currentOrderData.tx_explorer_url = explorerUrl;
        currentOrderData.solana_reference = pay.reference;
        currentOrderData.amount_sol = pay.amountSol;
        currentOrderData.network = "devnet";
        currentOrderData.bukti_bayar = null;

        await db.collection("orders").add(currentOrderData);

        document.getElementById('qrisModal').style.display = 'none';
        alert("✅ Pembayaran terverifikasi & pesanan terkirim! Admin akan memproses pesanan Anda.");
        location.href = "/";
    } catch(error){
        console.error("Gagal simpan pesanan:", error);
        orderSaved = false;
        if(percobaan < 3){
            setStatus("⚠️ Gagal menyimpan, mencoba lagi...");
            setTimeout(() => simpanPesananSetelahBayar(pay, percobaan + 1), 2000);
        } else {
            setStatus("❌ Pembayaran sudah masuk (signature: " + pay.signature.slice(0,12) + "...) tapi pesanan gagal disimpan. Jangan bayar lagi, hubungi admin.");
        }
    }
}

/* =========================
   TUTUP MODAL
========================= */
function tutupModal(tanpaKonfirmasi = false){
    if(tanpaKonfirmasi === true || confirm("⚠️ Apakah Anda yakin ingin membatalkan pembayaran?")){
        if(window.SolanaPay) window.SolanaPay.stop();
        document.getElementById('qrisModal').style.display = 'none';
        document.getElementById('qr-container').innerHTML = '';
    }
}

/* =========================
   HELPER: FORMAT RUPIAH
========================= */
function formatRupiah(angka){
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

</script>
@endpush