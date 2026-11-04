@extends('layouts.app')

@section('title','Pesanan Saya')

@section('content')

<div class="section-heading">
    <h2>Pesanan Saya</h2>
</div>

<div id="listOrder" class="produk-grid"></div>

<div id="emptyOrder" class="empty-state-box" style="display:none;">
    <div class="empty-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>
    </div>
    <h3>Belum ada pesanan</h3>
    <p>Yuk mulai belanja produk aluminium pilihan kami.</p>
</div>

@endsection

@push('scripts')
<script>

/* format signature transaksi Solana (supaya data yang dimanipulasi tidak ditampilkan) */
const SIG_REGEX = /^[1-9A-HJ-NP-Za-km-z]{64,90}$/;

/* ======================
   ESCAPE HTML (cegah teks berisi tag HTML ikut dirender)
====================== */
function esc(value){
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}

auth.onAuthStateChanged(async user => {

    if(!user){
        location.href="/login";
        return;
    }

    const snap = await db.collection("users").doc(user.uid).get();
    const role = snap.exists ? snap.data().role : "user";

    if(role === "admin"){
        location.href = "/admin/pesanan";
        return;
    }

    db.collection("orders")
    .where("user_id","==",user.uid)
    .orderBy("created_at","desc")
    .onSnapshot(snapshot => {

        let html = "";
        let count = 0;

        snapshot.forEach(doc => {

            const o = doc.data();

            // sembunyikan kalau sudah dibatalkan
            if(o.status === "cancelled") return;

            count++;

            const statusInfo = mapStatus(o);

            // bukti transaksi blockchain (Solana Pay)
            const sigValid = o.tx_signature && SIG_REGEX.test(o.tx_signature);
            const hasPayment = !!sigValid;

            const buktiHtml = sigValid
                ? `<p style="font-size:13px;margin:6px 0;">
                       <a href="https://explorer.solana.com/tx/${o.tx_signature}?cluster=devnet"
                          target="_blank" rel="noopener"
                          style="color:#2563eb;font-weight:600;text-decoration:none;">
                          🔗 Lihat bukti di Solana Explorer
                       </a><br>
                       <span style="color:#64748b;">${esc(o.amount_sol || '-')} SOL (devnet)</span>
                   </p>`
                : '';

            // alasan penolakan pembayaran (kalau ada)
            const rejectHtml = (o.payment_status === "rejected" && o.rejection_reason)
                ? `<p style="font-size:13px;color:#dc2626;margin:6px 0;">Alasan: ${esc(o.rejection_reason)}</p>`
                : '';

            // tombol batal: hanya selama admin belum menyetujui
            const canCancel = o.status === "pending" || o.status === "pending_payment";

            html += `
            <div class="produk-card">
                <div class="produk-info">
                    <h4>${esc(o.nama_produk)}</h4>

                    <p class="harga">Rp ${Number(o.harga).toLocaleString('id-ID')}</p>

                    <p style="font-size:13px;color:#64748b;margin-bottom:6px;">Nama: ${esc(o.nama_pemesan)}</p>

                    <span class="order-status ${statusInfo.cls}">
                        ${statusInfo.label}
                    </span>

                    ${buktiHtml}
                    ${rejectHtml}

                    ${
                        canCancel
                        ? `<div style="margin-top:12px;">
                            <button onclick="cancelOrder('${doc.id}', ${hasPayment})" class="btn-delete" style="width:100%;">
                                Batalkan Pesanan
                            </button>
                           </div>`
                        : ''
                    }
                </div>
            </div>
            `;
        });

        document.getElementById("listOrder").innerHTML = html;
        document.getElementById("emptyOrder").style.display = count === 0 ? "block" : "none";

    }, error => {
        console.error("Gagal memuat pesanan:", error);
        document.getElementById("listOrder").innerHTML =
            `<p style="color:#dc2626;">Gagal memuat pesanan: ${esc(error.message)}</p>`;
    });

});


/* ======================
   MAP STATUS -> label & css class
   (gabungan `status` + `payment_status`)
====================== */
function mapStatus(o){
    if(o.status === "cancelled"){
        return { label: "Dibatalkan", cls: "cancelled" };
    }
    if(o.payment_status === "rejected"){
        return { label: "Pembayaran Ditolak", cls: "cancelled" };
    }
    if(o.status === "completed"){
        return { label: "Selesai", cls: "selesai" };
    }
    if(o.status === "shipped"){
        return { label: "Dikirim", cls: "dikirim" };
    }
    if(o.status === "processing" || o.payment_status === "paid"){
        return { label: "Diproses", cls: "diproses" };
    }
    if(o.payment_status === "waiting_confirmation"){
        return { label: "Menunggu Verifikasi Admin", cls: "pending" };
    }
    return { label: "Belum Bayar", cls: "pending" };
}

/* ======================
   CANCEL ORDER
   Hanya mengirim status + cancelled_at (sesuai Firestore Rules).
   Hanya bisa selama admin belum menyetujui.
====================== */
function cancelOrder(id, hasPayment){

    const pesan = hasPayment
        ? "Batalkan pesanan ini?\n\nPembayaran SOL yang sudah masuk TIDAK dikembalikan otomatis. Admin akan memproses pengembalian secara manual."
        : "Batalkan pesanan ini?";

    if(!confirm(pesan)) return;

    db.collection("orders").doc(id).update({
        status: "cancelled",
        cancelled_at: firebase.firestore.FieldValue.serverTimestamp()
    }).catch(error => {
        console.error("Gagal membatalkan:", error);
        alert("Gagal membatalkan pesanan. Kemungkinan pesanan sudah disetujui admin.");
    });
}

</script>
@endpush