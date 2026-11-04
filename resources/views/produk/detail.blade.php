@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')

<div class="detail-container" id="detailProduk">
    <p>Memuat produk...</p>
</div>

@endsection

@push('scripts')
<script>
const productId = "{{ $id }}";
const container = document.getElementById("detailProduk");

db.collection("product").doc(productId).get()
.then(doc => {
    if (!doc.exists) {
        container.innerHTML = "<p>Produk tidak ditemukan</p>";
        return;
    }

    const p = doc.data();

    container.innerHTML = `
        <div class="detail-image">
            <img src="${p.gambar}" alt="${p.nama}">
        </div>
        <div class="detail-info">
            <h2>${p.nama}</h2>
            <span class="price">Rp ${Number(p.harga).toLocaleString('id-ID')}</span>
            <p>${p.deskripsi}</p>
            <a href="/order/{{ $id }}" class="detail-btn">
                Pesan Sekarang
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    `;
});
</script>
@endpush
