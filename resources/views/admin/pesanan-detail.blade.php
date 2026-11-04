@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')
@section('page-subtitle', 'Informasi lengkap pesanan customer')

@section('content')

<div class="admin-page" style="max-width:1400px;">

    <!-- Loading State -->
    <div id="loadingState" class="loading-state">
        <div class="spinner"></div>
        <p>Memuat detail pesanan...</p>
    </div>

    <!-- Order Detail Container -->
    <div id="orderDetailContainer" style="display:none;">

        <!-- Header -->
        <div class="detail-header">
            <div>
                <h1>Order #<span id="orderNumber"></span></h1>
                <p id="orderDate"></p>
            </div>
            <div id="statusBadgeContainer"></div>
        </div>

        <div class="detail-grid">

            <!-- LEFT COLUMN -->
            <div class="detail-column">

                <!-- Customer Info Card -->
                <div class="detail-card">
                    <h3>Informasi Customer</h3>
                    <div class="info-row">
                        <span class="info-label">Nama:</span>
                        <span class="info-value" id="customerName"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">No HP:</span>
                        <span class="info-value">
                            <span id="customerPhone"></span>
                            <a href="#" id="waLink" target="_blank" class="btn-whatsapp">
                                Hubungi WA
                            </a>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value" id="customerEmail"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alamat:</span>
                        <span class="info-value" id="customerAddress"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Catatan:</span>
                        <span class="info-value" id="orderNotes"></span>
                    </div>
                </div>

                <!-- Map Card -->
                <div class="detail-card">
                    <h3>Lokasi Pengiriman</h3>
                    <div id="mapContainer">
                        <iframe id="mapFrame"
                                width="100%"
                                height="300"
                                frameborder="0"
                                style="border:0; border-radius:12px;"
                                allowfullscreen>
                        </iframe>
                    </div>
                    <div class="info-row" style="margin-top:10px;">
                        <span class="info-label">Koordinat:</span>
                        <span class="info-value" id="coordinates"></span>
                    </div>
                </div>

                <!-- Product Info Card -->
                <div class="detail-card">
                    <h3>Informasi Produk</h3>
                    <div class="info-row">
                        <span class="info-label">Nama Produk:</span>
                        <span class="info-value" id="productName"></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Harga:</span>
                        <span class="info-value" id="productPrice"></span>
                    </div>
                    <div class="info-row total-row">
                        <span class="info-label"><strong>TOTAL:</strong></span>
                        <span class="info-value" id="totalPrice"></span>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="detail-column">

                <!-- Payment Info Card -->
                <div class="detail-card">
                    <h3>Informasi Pembayaran</h3>

                    <div class="info-row">
                        <span class="info-label">Metode:</span>
                        <span class="info-value" id="paymentMethod">-</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Status Payment:</span>
                        <span class="info-value" id="paymentStatusText"></span>
                    </div>

                    <div class="info-row" id="paymentDateRow" style="display:none;">
                        <span class="info-label">Tanggal Pembayaran:</span>
                        <span class="info-value" id="paymentDate"></span>
                    </div>

                    <div class="info-row" id="confirmedDateRow" style="display:none;">
                        <span class="info-label">Dikonfirmasi:</span>
                        <span class="info-value" id="confirmedDate"></span>
                    </div>

                    <div class="info-row" id="rejectionReasonRow" style="display:none;">
                        <span class="info-label">Alasan Ditolak:</span>
                        <span class="info-value" id="rejectionReason"></span>
                    </div>

                    <!-- Bukti Transaksi Blockchain (Solana Pay) -->
                    <div id="blockchainSection" style="display:none;">
                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eef1f6;">
                        <h4 style="margin-bottom: 15px;">Bukti Transaksi Blockchain</h4>

                        <div class="info-row">
                            <span class="info-label">Jaringan:</span>
                            <span class="info-value" id="chainNetwork"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Jumlah:</span>
                            <span class="info-value" id="chainAmount"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Signature:</span>
                            <span class="info-value" id="chainSignature" style="word-break:break-all; font-size:12px;"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Verifikasi:</span>
                            <span class="info-value" id="chainVerify" style="word-break:break-word;">-</span>
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
                            <a id="explorerLink"
                               href="#"
                               target="_blank"
                               rel="noopener"
                               class="btn-action btn-update"
                               style="text-decoration:none; display:inline-block;">
                                Buka di Solana Explorer
                            </a>
                            <button type="button" onclick="verifyOnChain()" class="btn-action btn-update">
                                Cek Ulang Blockchain
                            </button>
                        </div>
                    </div>

                    <!-- Bukti Pembayaran (gambar, untuk pesanan lama) -->
                    <div id="paymentProofSection" style="display:none;">
                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eef1f6;">
                        <h4 style="margin-bottom: 15px;">Bukti Pembayaran</h4>
                        <div class="payment-proof-container">
                            <img id="paymentProof"
                                 src=""
                                 alt="Bukti Bayar"
                                 onclick="viewImageFullscreen(this.src)">
                            <p style="text-align: center; margin-top: 10px; color: #64748b; font-size: 13px;">
                                Klik gambar untuk memperbesar
                            </p>
                        </div>
                    </div>

                    <div id="noPaymentProof" style="display:none; text-align:center; padding:30px; color:#94a3b8;">
                        <p id="noPaymentProofText">Belum ada bukti pembayaran</p>
                    </div>

                </div>

                <!-- Action Buttons Card -->
                <div class="detail-card" id="actionCard" style="display:none;">
                    <h3>Aksi</h3>

                    <div class="action-buttons">
                        <button onclick="approvePayment()"
                                class="btn-action btn-approve"
                                id="btnApprove">
                            Approve Pembayaran
                        </button>

                        <button onclick="rejectPayment()"
                                class="btn-action btn-reject"
                                id="btnReject">
                            Reject Pembayaran
                        </button>
                    </div>

                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #eef1f6;">

                    <div class="status-update-section">
                        <label for="statusSelect"><strong>Update Status Order:</strong></label>
                        <select id="statusSelect" class="status-select">
                            <option value="pending_payment">Belum Bayar</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Dikirim</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        <button onclick="updateOrderStatus()" class="btn-action btn-update">
                            Update Status
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')
<script>

const orderId = "{{ $id }}"; // Dari route parameter
let orderData = null;

/* =========================
   PENGATURAN BLOCKCHAIN (devnet)
   Harus SAMA dengan resources/js/solana-pay.js
========================= */
const STORE_WALLET   = "BYHRNDC2ScR86ZXvo5bre9nTKoPR5xkyZsdKXdEidsdY"; // alamat publik wallet toko
const RUPIAH_PER_SOL = 5000000;                                        // kurs tetap demo
const SOLANA_RPC     = "https://api.devnet.solana.com";
const SIG_REGEX      = /^[1-9A-HJ-NP-Za-km-z]{64,90}$/;                // format signature Solana

/* =========================
   STATUS PESANAN (gabungan `status` + `payment_status`)
   Dipakai untuk badge header. `status` (alur pesanan) diprioritaskan
   di atas `payment_status` (verifikasi bayar) supaya pesanan yang
   sudah Dibatalkan/Selesai/Dikirim tidak "nyangkut" di Belum Bayar.
========================= */
function getOrderStatusBadge(order) {
    if (order.status === 'cancelled') {
        return { class: 'status-rejected', text: 'Dibatalkan' };
    }
    if (order.payment_status === 'rejected') {
        return { class: 'status-rejected', text: 'Pembayaran Ditolak' };
    }
    if (order.status === 'completed') {
        return { class: 'status-paid', text: 'Selesai' };
    }
    if (order.status === 'shipped') {
        return { class: 'status-paid', text: 'Dikirim' };
    }
    if (order.status === 'processing' || order.payment_status === 'paid') {
        return { class: 'status-paid', text: 'Sudah Dibayar' };
    }
    if (order.payment_status === 'waiting_confirmation') {
        return { class: 'status-waiting', text: 'Menunggu Verifikasi' };
    }
    return { class: 'status-pending', text: 'Belum Bayar' };
}

// Load order detail on page load
document.addEventListener('DOMContentLoaded', () => {
    loadOrderDetail();
});

/* =========================
   LOAD ORDER DETAIL
========================= */
async function loadOrderDetail() {
    try {
        const doc = await db.collection('orders').doc(orderId).get();

        if (!doc.exists) {
            alert('Order tidak ditemukan!');
            window.location.href = '/admin/orders';
            return;
        }

        orderData = {
            id: doc.id,
            ...doc.data()
        };

        displayOrderDetail();

    } catch (error) {
        console.error('Error loading order:', error);
        document.getElementById('loadingState').innerHTML = `
            <p style="color: red;">❌ Gagal memuat detail: ${error.message}</p>
            <button onclick="loadOrderDetail()" class="btn-action btn-update">Coba Lagi</button>
        `;
    }
}

/* =========================
   DISPLAY ORDER DETAIL
========================= */
function displayOrderDetail() {
    // Hide loading, show content
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('orderDetailContainer').style.display = 'block';

    // Header
    document.getElementById('orderNumber').textContent = orderData.order_number || orderData.id.substring(0, 8);
    
    const orderDate = orderData.created_at ? 
        new Date(orderData.created_at.toDate()).toLocaleString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }) : '-';
    document.getElementById('orderDate').textContent = orderDate;

    // Status Badge (header) — status pesanan keseluruhan, termasuk Dibatalkan/Selesai/Dikirim
    const headerBadge = getOrderStatusBadge(orderData);
    document.getElementById('statusBadgeContainer').innerHTML =
        `<span class="status-badge ${headerBadge.class}">${headerBadge.text}</span>`;

    // Status khusus pembayaran (untuk panel "Informasi Pembayaran")
    let statusClass = 'status-pending';
    let statusText = 'Belum Bayar';

    if (orderData.payment_status === 'waiting_confirmation') {
        statusClass = 'status-waiting';
        statusText = 'Menunggu Verifikasi';
    } else if (orderData.payment_status === 'paid') {
        statusClass = 'status-paid';
        statusText = 'Sudah Dibayar';
    } else if (orderData.payment_status === 'rejected') {
        statusClass = 'status-rejected';
        statusText = 'Ditolak';
    }

    // Customer Info
    document.getElementById('customerName').textContent = orderData.nama_pemesan;
    document.getElementById('customerPhone').textContent = orderData.no_hp;
    document.getElementById('customerEmail').textContent = orderData.user_email || '-';
    document.getElementById('customerAddress').textContent = orderData.alamat;
    document.getElementById('orderNotes').textContent = orderData.catatan || '-';

    // WhatsApp Link
    const waNumber = orderData.no_hp.replace(/^0/, '62');
    document.getElementById('waLink').href = `https://wa.me/${waNumber}?text=Halo ${orderData.nama_pemesan}, mengenai pesanan Anda...`;

    // Map
    if (orderData.lat && orderData.lng) {
        const mapUrl = `https://www.google.com/maps?q=${orderData.lat},${orderData.lng}&output=embed`;
        document.getElementById('mapFrame').src = mapUrl;
        document.getElementById('coordinates').textContent = `${orderData.lat}, ${orderData.lng}`;
    } else {
        document.getElementById('mapContainer').innerHTML = '<p style="color:#999;">Koordinat tidak tersedia</p>';
    }

    // Product Info
    document.getElementById('productName').textContent = orderData.nama_produk;
    document.getElementById('productPrice').textContent = formatRupiah(orderData.harga);
    document.getElementById('totalPrice').textContent = formatRupiah(orderData.harga);

    // Payment Info
    const methodLabels = {
        solana_pay: 'Solana Pay (Devnet)',
        qris: 'QRIS (upload bukti manual)'
    };
    document.getElementById('paymentMethod').textContent =
        methodLabels[orderData.payment_method] || orderData.payment_method || '-';

    document.getElementById('paymentStatusText').innerHTML = 
        `<span class="status-badge ${statusClass}">${statusText}</span>`;

    if (orderData.payment_date) {
        document.getElementById('paymentDateRow').style.display = 'flex';
        const paymentDate = new Date(orderData.payment_date.toDate()).toLocaleString('id-ID');
        document.getElementById('paymentDate').textContent = paymentDate;
    }

    if (orderData.payment_confirmed_at) {
        document.getElementById('confirmedDateRow').style.display = 'flex';
        const confirmedDate = new Date(orderData.payment_confirmed_at.toDate()).toLocaleString('id-ID');
        document.getElementById('confirmedDate').textContent = confirmedDate;
    }

    if (orderData.payment_status === 'rejected' && orderData.rejection_reason) {
        document.getElementById('rejectionReasonRow').style.display = 'flex';
        document.getElementById('rejectionReason').textContent = orderData.rejection_reason;
    }

    // Bukti pembayaran: transaksi blockchain > gambar lama > belum ada
    const hasSignature = !!orderData.tx_signature;
    const sigValid = hasSignature && SIG_REGEX.test(orderData.tx_signature);

    document.getElementById('blockchainSection').style.display = 'none';
    document.getElementById('paymentProofSection').style.display = 'none';
    document.getElementById('noPaymentProof').style.display = 'none';

    if (sigValid) {
        document.getElementById('blockchainSection').style.display = 'block';
        document.getElementById('chainNetwork').textContent = 'Solana ' + (orderData.network || 'devnet');
        document.getElementById('chainAmount').textContent =
            (orderData.amount_sol || '-') + ' SOL';
        document.getElementById('chainSignature').textContent = orderData.tx_signature;
        document.getElementById('explorerLink').href =
            'https://explorer.solana.com/tx/' + orderData.tx_signature + '?cluster=devnet';

        // verifikasi otomatis ke blockchain saat halaman dibuka
        verifyOnChain();
    } else if (hasSignature) {
        // signature ada tapi formatnya tidak valid (kemungkinan dimanipulasi)
        document.getElementById('noPaymentProof').style.display = 'block';
        document.getElementById('noPaymentProofText').textContent =
            '⚠️ Signature transaksi tidak valid (format salah). Jangan approve pesanan ini.';
    } else if (orderData.bukti_bayar) {
        document.getElementById('paymentProofSection').style.display = 'block';
        document.getElementById('paymentProof').src = orderData.bukti_bayar;
    } else {
        document.getElementById('noPaymentProof').style.display = 'block';
        document.getElementById('noPaymentProofText').textContent = 'Belum ada bukti pembayaran';
    }

    // Action Buttons
    if (orderData.payment_status === 'waiting_confirmation') {
        document.getElementById('actionCard').style.display = 'block';
        document.getElementById('btnApprove').disabled = false;
        document.getElementById('btnReject').disabled = false;
    } else {
        document.getElementById('actionCard').style.display = 'block';
        document.getElementById('btnApprove').disabled = true;
        document.getElementById('btnReject').disabled = true;
    }

    // Status Select
    document.getElementById('statusSelect').value = orderData.status || 'pending_payment';
}

/* =========================
   VERIFIKASI TRANSAKSI DI BLOCKCHAIN
   Memeriksa langsung ke jaringan devnet (bukan percaya data di Firestore):
   - transaksi ada dan berhasil
   - wallet toko menerima SOL
   - jumlahnya cukup untuk harga produk
========================= */
async function verifyOnChain() {
    const el = document.getElementById('chainVerify');
    if (!orderData || !orderData.tx_signature || !SIG_REGEX.test(orderData.tx_signature)) {
        el.textContent = '-';
        return;
    }

    el.style.color = '#64748b';
    el.textContent = '⏳ Memeriksa blockchain...';

    try {
        const res = await fetch(SOLANA_RPC, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                jsonrpc: '2.0',
                id: 1,
                method: 'getTransaction',
                params: [
                    orderData.tx_signature,
                    { encoding: 'jsonParsed', commitment: 'confirmed', maxSupportedTransactionVersion: 0 }
                ]
            })
        });

        if (!res.ok) throw new Error('RPC ' + res.status);
        const json = await res.json();
        if (json.error) throw new Error(json.error.message || 'RPC error');

        const tx = json.result;

        if (!tx) {
            el.style.color = '#dc2626';
            el.textContent = '❌ Transaksi TIDAK ditemukan di devnet. Jangan approve.';
            return;
        }
        if (tx.meta && tx.meta.err) {
            el.style.color = '#dc2626';
            el.textContent = '❌ Transaksi ada tetapi GAGAL di blockchain. Jangan approve.';
            return;
        }

        const keys = tx.transaction.message.accountKeys.map(k => k.pubkey || k);
        const idx = keys.indexOf(STORE_WALLET);

        if (idx === -1) {
            el.style.color = '#dc2626';
            el.textContent = '❌ Transaksi ini BUKAN ke wallet toko. Jangan approve.';
            return;
        }

        const received = tx.meta.postBalances[idx] - tx.meta.preBalances[idx]; // lamports

        // jumlah minimal dari HARGA produk (bukan dari data yang ditulis user)
        const minLamports = Math.ceil(Number(orderData.harga) * 1e4 / RUPIAH_PER_SOL) * 1e5;

        const receivedSol = (received / 1e9).toFixed(9).replace(/0+$/, '').replace(/\.$/, '');
        const minSol = (minLamports / 1e9).toFixed(4);

        if (received < minLamports) {
            el.style.color = '#dc2626';
            el.textContent = `❌ Jumlah kurang: toko menerima ${receivedSol} SOL, seharusnya minimal ${minSol} SOL. Jangan approve.`;
            return;
        }

        const waktu = tx.blockTime
            ? new Date(tx.blockTime * 1000).toLocaleString('id-ID')
            : '-';

        el.style.color = '#16a34a';
        el.textContent = `✅ Valid: wallet toko menerima ${receivedSol} SOL (minimal ${minSol} SOL). Waktu: ${waktu}`;

    } catch (error) {
        console.error('Verifikasi blockchain gagal:', error);
        el.style.color = '#d97706';
        el.textContent = '⚠️ Gagal memeriksa blockchain (' + error.message + '). Klik "Cek Ulang Blockchain" atau buka Solana Explorer.';
    }
}

/* =========================
   APPROVE PAYMENT
========================= */
async function approvePayment() {
    if (!confirm('✅ Approve pembayaran ini?\n\nPastikan hasil verifikasi blockchain berstatus valid.\nStatus order akan berubah menjadi "Processing".')) {
        return;
    }

    try {
        await db.collection('orders').doc(orderId).update({
            payment_status: 'paid',
            payment_confirmed_at: firebase.firestore.FieldValue.serverTimestamp(),
            payment_confirmed_by: auth.currentUser.uid,
            status: 'processing'
        });

        alert('✅ Pembayaran berhasil diapprove!');
        loadOrderDetail();

        // Optional: Kirim notif WA
        // sendWhatsAppNotification(orderData.no_hp, 'Pembayaran Anda telah dikonfirmasi!');

    } catch (error) {
        console.error('Error approving payment:', error);
        alert('❌ Gagal approve: ' + error.message);
    }
}

/* =========================
   REJECT PAYMENT
========================= */
async function rejectPayment() {
    const reason = prompt('❌ Alasan reject pembayaran:\n(Contoh: Transaksi tidak valid, Nominal tidak sesuai, dll)');
    
    if (!reason) return;

    try {
        await db.collection('orders').doc(orderId).update({
            payment_status: 'rejected',
            rejection_reason: reason,
            rejected_at: firebase.firestore.FieldValue.serverTimestamp(),
            rejected_by: auth.currentUser.uid
        });

        alert('❌ Pembayaran ditolak.');
        loadOrderDetail();

        // Optional: Kirim notif WA
        // sendWhatsAppNotification(orderData.no_hp, `Pembayaran ditolak: ${reason}`);

    } catch (error) {
        console.error('Error rejecting payment:', error);
        alert('❌ Gagal reject: ' + error.message);
    }
}

/* =========================
   UPDATE ORDER STATUS
========================= */
async function updateOrderStatus() {
    const newStatus = document.getElementById('statusSelect').value;
    
    if (!confirm(`Update status order menjadi "${newStatus}"?`)) {
        return;
    }

    try {
        await db.collection('orders').doc(orderId).update({
            status: newStatus,
            updated_at: firebase.firestore.FieldValue.serverTimestamp()
        });

        alert('✅ Status order berhasil diupdate!');
        loadOrderDetail();

    } catch (error) {
        console.error('Error updating status:', error);
        alert('❌ Gagal update status: ' + error.message);
    }
}

/* =========================
   VIEW IMAGE FULLSCREEN
========================= */
function viewImageFullscreen(url) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    `;

    modal.innerHTML = `
        <img src="${url}" 
             style="max-width: 95%; max-height: 95%; border-radius: 12px; box-shadow: 0 10px 50px rgba(0,0,0,0.5);">
        <div style="position: absolute; top: 20px; right: 20px; color: white; font-size: 40px; cursor: pointer;">
            ✕
        </div>
    `;

    modal.onclick = () => modal.remove();
    document.body.appendChild(modal);
}

/* =========================
   HELPER: FORMAT RUPIAH
========================= */
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

</script>
@endpush