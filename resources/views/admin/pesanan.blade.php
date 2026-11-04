@extends('layouts.admin')

@section('title', 'Daftar Pesanan')
@section('page-title', 'Daftar Pesanan')
@section('page-subtitle', 'Kelola semua pesanan customer')

@section('content')

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab active" onclick="filterOrders('all')" id="tab-all">
        Semua <span class="badge" id="count-all">0</span>
    </button>
    <button class="filter-tab" onclick="filterOrders('pending_payment')" id="tab-pending_payment">
        Belum Bayar <span class="badge badge-red" id="count-pending">0</span>
    </button>
    <button class="filter-tab" onclick="filterOrders('waiting_confirmation')" id="tab-waiting_confirmation">
        Menunggu Verifikasi <span class="badge badge-yellow" id="count-waiting">0</span>
    </button>
    <button class="filter-tab" onclick="filterOrders('paid')" id="tab-paid">
        Sudah Dibayar <span class="badge badge-green" id="count-paid">0</span>
    </button>
    <button class="filter-tab" onclick="filterOrders('rejected')" id="tab-rejected">
        Ditolak <span class="badge badge-gray" id="count-rejected">0</span>
    </button>
    <button class="filter-tab" onclick="filterOrders('cancelled')" id="tab-cancelled">
        Dibatalkan <span class="badge badge-gray" id="count-cancelled">0</span>
    </button>
</div>

<!-- Search Bar -->
<div class="admin-toolbar">
    <div class="admin-search">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="text"
               id="searchInput"
               placeholder="Cari berdasarkan nama, order ID, produk, atau signature..."
               onkeyup="searchOrders()">
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="loading-state">
    <div class="spinner"></div>
    <p>Memuat data pesanan...</p>
</div>

<!-- Empty State -->
<div id="emptyState" class="empty-state-box" style="display:none;">
    <div class="empty-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>
    </div>
    <h3>Belum Ada Pesanan</h3>
    <p>Pesanan akan muncul di sini setelah customer melakukan pemesanan.</p>
</div>

<!-- Orders Table -->
<div class="admin-table-card" id="ordersTableContainer" style="display:none;">
    <div class="table-responsive">
        <table id="ordersTable">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Produk</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Bukti Bayar / Tx Blockchain</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="ordersTableBody">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Modal Konfirmasi Custom (pengganti confirm() bawaan browser) */
.confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.6);
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: confirmFadeIn 0.15s ease-out;
}

@keyframes confirmFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.confirm-box {
    background: #fff;
    border-radius: 14px;
    padding: 28px;
    width: 90%;
    max-width: 380px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.35);
    text-align: center;
    animation: confirmPopIn 0.18s ease-out;
}

@keyframes confirmPopIn {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.confirm-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 24px;
}

.confirm-title {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
}

.confirm-message {
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 22px;
}

.confirm-actions {
    display: flex;
    gap: 10px;
}

.confirm-actions button {
    flex: 1;
    padding: 11px 16px;
    border-radius: 9px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: filter 0.15s;
}

.confirm-actions button:hover {
    filter: brightness(0.95);
}

.confirm-btn-cancel {
    background: #f1f5f9;
    color: #334155;
}

.confirm-btn-danger {
    background: #dc2626;
    color: #fff;
}
</style>
@endpush

@push('scripts')
<script>

let allOrders = [];
let currentFilter = 'all';

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

// ID dokumen Firestore normal hanya berisi huruf/angka. Selain itu dilewati.
const SAFE_ID = /^[A-Za-z0-9_-]{1,64}$/;

// hanya izinkan URL http/https (menolak javascript: dan sejenisnya)
function safeHttpUrl(url) {
    try {
        const u = new URL(url);
        return (u.protocol === 'https:' || u.protocol === 'http:') ? u.href : '';
    } catch (e) {
        return '';
    }
}

/* =========================
   STATUS PESANAN (gabungan `status` + `payment_status`)
   Sama seperti di admin/pesanan-detail.blade.php — `status` (alur
   pesanan: cancelled/completed/shipped/processing) diprioritaskan
   di atas `payment_status` (verifikasi bayar) supaya pesanan yang
   sudah Dibatalkan tidak tetap kelihatan "Belum Bayar".
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

// Load orders on page load
document.addEventListener('DOMContentLoaded', () => {
    loadOrders();
});

/* =========================
   LOAD ALL ORDERS
========================= */
async function loadOrders() {
    try {
        // Show loading
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('ordersTableContainer').style.display = 'none';

        // Get all orders from Firestore
        const snapshot = await db.collection('orders')
            .orderBy('created_at', 'desc')
            .get();

        allOrders = [];
        snapshot.forEach(doc => {
            allOrders.push({
                id: doc.id,
                ...doc.data()
            });
        });

        // Hide loading
        document.getElementById('loadingState').style.display = 'none';

        // Update counts
        updateCounts();

        // Show orders or empty state
        if (allOrders.length === 0) {
            document.getElementById('emptyState').style.display = 'block';
        } else {
            document.getElementById('ordersTableContainer').style.display = 'block';
            displayOrders();
        }

    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('loadingState').innerHTML = `
            <p style="color: #dc2626;">Gagal memuat data: ${error.message}</p>
            <button onclick="loadOrders()" class="btn-outline">Coba Lagi</button>
        `;
    }
}

/* =========================
   UPDATE COUNTS
========================= */
function updateCounts() {
    const counts = {
        all: allOrders.length,
        // Belum Bayar: belum ada payment_status sama sekali & belum dibatalkan
        pending_payment: allOrders.filter(o => o.status !== 'cancelled' && !o.payment_status).length,
        waiting_confirmation: allOrders.filter(o => o.status !== 'cancelled' && o.payment_status === 'waiting_confirmation').length,
        paid: allOrders.filter(o => o.status !== 'cancelled' && o.payment_status === 'paid').length,
        rejected: allOrders.filter(o => o.payment_status === 'rejected').length,
        cancelled: allOrders.filter(o => o.status === 'cancelled').length
    };

    document.getElementById('count-all').textContent = counts.all;
    document.getElementById('count-pending').textContent = counts.pending_payment;
    document.getElementById('count-waiting').textContent = counts.waiting_confirmation;
    document.getElementById('count-paid').textContent = counts.paid;
    document.getElementById('count-rejected').textContent = counts.rejected;
    document.getElementById('count-cancelled').textContent = counts.cancelled;
}

/* =========================
   FILTER ORDERS
========================= */
function filterOrders(filter) {
    currentFilter = filter;

    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.getElementById(`tab-${filter}`).classList.add('active');

    // Display filtered orders
    displayOrders();
}

/* =========================
   DISPLAY ORDERS
========================= */
function displayOrders() {
    // Filter orders based on current filter
    let filteredOrders = allOrders;

    if (currentFilter !== 'all') {
        if (currentFilter === 'pending_payment') {
            filteredOrders = allOrders.filter(o => o.status !== 'cancelled' && !o.payment_status);
        } else if (currentFilter === 'cancelled') {
            filteredOrders = allOrders.filter(o => o.status === 'cancelled');
        } else if (currentFilter === 'waiting_confirmation' || currentFilter === 'paid') {
            filteredOrders = allOrders.filter(o => o.status !== 'cancelled' && o.payment_status === currentFilter);
        } else {
            filteredOrders = allOrders.filter(o => o.payment_status === currentFilter);
        }
    }

    // Apply search filter
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    if (searchTerm) {
        filteredOrders = filteredOrders.filter(order => {
            return (
                String(order.nama_pemesan || '').toLowerCase().includes(searchTerm) ||
                String(order.nama_produk || '').toLowerCase().includes(searchTerm) ||
                String(order.order_number || '').toLowerCase().includes(searchTerm) ||
                (order.tx_signature || '').toLowerCase().includes(searchTerm) ||
                order.id.toLowerCase().includes(searchTerm)
            );
        });
    }

    // Render table
    const tbody = document.getElementById('ordersTableBody');
    tbody.innerHTML = '';

    if (filteredOrders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8;">
                    Tidak ada pesanan untuk filter ini.
                </td>
            </tr>
        `;
        return;
    }

    filteredOrders.forEach(order => {
        if (!SAFE_ID.test(order.id)) return; // abaikan dokumen dengan ID mencurigakan

        const row = document.createElement('tr');

        // Format tanggal
        const orderDate = (order.created_at && typeof order.created_at.toDate === 'function') ?
            new Date(order.created_at.toDate()).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : '-';

        // Status badge — gabungan status pesanan & payment_status
        const badge = getOrderStatusBadge(order);
        const statusClass = badge.class;
        const statusText = badge.text;

        // Bukti bayar: prioritaskan transaksi blockchain, fallback ke gambar lama.
        // Regex memastikan hanya format signature Solana yang valid yang ditampilkan
        // (data ditulis dari browser user, jadi tidak boleh dipercaya begitu saja).
        const sigValid = order.tx_signature &&
            /^[1-9A-HJ-NP-Za-km-z]{64,90}$/.test(order.tx_signature);

        let buktiBayar;
        if (sigValid) {
            buktiBayar = `
                <a href="https://explorer.solana.com/tx/${order.tx_signature}?cluster=devnet"
                   target="_blank" rel="noopener"
                   style="color:#2563eb; font-weight:600; text-decoration:none;">
                    🔗 ${order.tx_signature.slice(0, 6)}…${order.tx_signature.slice(-4)}
                </a><br>
                <small style="color:#64748b;">${esc(order.amount_sol || '-')} SOL (devnet)</small>`;
        } else if (safeHttpUrl(order.bukti_bayar)) {
            buktiBayar = `<img src="${esc(safeHttpUrl(order.bukti_bayar))}"
                               class="payment-thumb"
                               onclick="viewImage(this.src)"
                               alt="Bukti Bayar">`;
        } else {
            buktiBayar = `<span class="no-proof">Belum ada bukti</span>`;
        }

        // Action buttons
        let actionButtons = `
            <div class="action-btns">
                <a href="/admin/orders/${order.id}" class="btn-detail-sm">Detail</a>
        `;

        if (order.payment_status === 'waiting_confirmation') {
            actionButtons += `
                <button onclick="quickApprove('${order.id}')" class="btn-approve" title="Approve">✓</button>
                <button onclick="quickReject('${order.id}')" class="btn-reject" title="Reject">✕</button>
            `;
        }

        // Tombol Hapus — hanya muncul untuk pesanan yang statusnya sudah final
        // (Selesai, Dikirim, Dibatalkan, atau Pembayaran Ditolak)
        const isFinishedStatus = order.status === 'cancelled'
            || order.status === 'completed'
            || order.status === 'shipped'
            || order.payment_status === 'rejected';

        if (isFinishedStatus) {
            actionButtons += `
                <button onclick="deleteOrder('${order.id}')" class="btn-reject" title="Hapus Pesanan">🗑️</button>
            `;
        }

        actionButtons += `</div>`;

        row.innerHTML = `
            <td><strong>${esc(order.order_number || order.id.substring(0, 8))}</strong></td>
            <td>${orderDate}</td>
            <td>
                <strong>${esc(order.nama_pemesan)}</strong><br>
                <small style="color:#64748b;">${esc(order.no_hp)}</small>
            </td>
            <td>${esc(order.nama_produk)}</td>
            <td><strong>${formatRupiah(order.harga)}</strong></td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>${buktiBayar}</td>
            <td>${actionButtons}</td>
        `;

        tbody.appendChild(row);
    });
}

/* =========================
   SEARCH ORDERS
========================= */
function searchOrders() {
    displayOrders();
}

/* =========================
   QUICK APPROVE
========================= */
async function quickApprove(orderId) {
    if (!confirm('Sudah cek transaksi di Solana Explorer dan nominalnya sesuai? Approve pembayaran ini?')) return;

    try {
        await db.collection('orders').doc(orderId).update({
            payment_status: 'paid',
            payment_confirmed_at: firebase.firestore.FieldValue.serverTimestamp(),
            payment_confirmed_by: auth.currentUser.uid,
            status: 'processing'
        });

        alert('Pembayaran berhasil diapprove!');
        loadOrders();
    } catch (error) {
        console.error('Error approving:', error);
        alert('Gagal approve: ' + error.message);
    }
}

/* =========================
   QUICK REJECT
========================= */
async function quickReject(orderId) {
    const reason = prompt('Alasan reject pembayaran:');
    if (!reason) return;

    try {
        await db.collection('orders').doc(orderId).update({
            payment_status: 'rejected',
            rejection_reason: reason,
            rejected_at: firebase.firestore.FieldValue.serverTimestamp(),
            rejected_by: auth.currentUser.uid
        });

        alert('Pembayaran ditolak.');
        loadOrders();
    } catch (error) {
        console.error('Error rejecting:', error);
        alert('Gagal reject: ' + error.message);
    }
}

/* =========================
   CUSTOM CONFIRM MODAL
   Pengganti confirm() bawaan browser, tampil di tengah & modern.
   Dipakai dengan: showConfirmModal({ title, message, onConfirm })
========================= */
function showConfirmModal({ title, message, confirmText = 'Ya, Hapus', onConfirm }) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';

    overlay.innerHTML = `
        <div class="confirm-box">
            <div class="confirm-icon">🗑️</div>
            <div class="confirm-title">${title}</div>
            <div class="confirm-message">${message}</div>
            <div class="confirm-actions">
                <button class="confirm-btn-cancel" id="confirmCancelBtn">Batal</button>
                <button class="confirm-btn-danger" id="confirmOkBtn">${confirmText}</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const close = () => overlay.remove();

    overlay.querySelector('#confirmCancelBtn').onclick = close;
    overlay.onclick = (e) => { if (e.target === overlay) close(); };
    overlay.querySelector('#confirmOkBtn').onclick = () => {
        close();
        onConfirm();
    };
}

/* =========================
   DELETE ORDER
   Hanya dipanggil untuk pesanan berstatus final
   (Selesai / Dikirim / Dibatalkan / Ditolak)
========================= */
function deleteOrder(orderId) {
    showConfirmModal({
        title: 'Hapus Pesanan?',
        message: 'Pesanan ini akan dihapus secara permanen dan tidak bisa dikembalikan.',
        confirmText: 'Ya, Hapus',
        onConfirm: async () => {
            try {
                await db.collection('orders').doc(orderId).delete();
                alert('Pesanan berhasil dihapus.');
                loadOrders();
            } catch (error) {
                console.error('Error deleting order:', error);
                alert('Gagal menghapus pesanan: ' + error.message);
            }
        }
    });
}

/* =========================
   VIEW IMAGE MODAL
========================= */
function viewImage(url) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    `;

    modal.innerHTML = `
        <img src="${esc(url)}"
             style="max-width: 90%; max-height: 90%; border-radius: 12px;">
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