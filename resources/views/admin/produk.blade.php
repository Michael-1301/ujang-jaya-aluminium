@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')
@section('page-subtitle', 'Lengkapi detail produk aluminium baru')

@section('content')

<div class="admin-page">
    <div class="admin-card">

        <h2>Detail Produk</h2>

        <form class="admin-form" id="formTambah">

            <label>Nama Produk</label>
            <input type="text" id="nama" required placeholder="Contoh: Pintu Aluminium Sliding">

            <label>Harga</label>
            <input type="number" id="harga" required placeholder="Contoh: 1500000">

            <label>Masukkan Gambar</label>
            <input type="file" id="gambarInput" accept="image/*">

            <!-- preview gambar -->
            <img id="previewImg" style="margin-top:10px;max-width:200px;display:none;border-radius:10px;box-shadow:var(--shadow-sm);">

            <label>Deskripsi</label>
            <textarea id="deskripsi" placeholder="Tuliskan spesifikasi dan keunggulan produk..."></textarea>

            <div class="form-actions">
                <button type="submit" class="btn-save" id="btnSubmit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Simpan
                </button>
                <a href="/admin" class="btn-cancel">Batal</a>
            </div>

        </form>

    </div>
</div>

@endsection


@push('scripts')
<script>

/* ===============================
   preview gambar
================================ */
gambarInput.addEventListener("change", (e)=>{
    const file = e.target.files[0];

    if(file){
        previewImg.src = URL.createObjectURL(file);
        previewImg.style.display = "block";
    }
});


/* ===============================
   fungsi upload gambar cloudinary ke laravel
================================ */
async function uploadGambar(file){

    const formData = new FormData();
    formData.append("image", file);

    const res = await fetch("/upload-image", {
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    });

    const data = await res.json();

    return data.url;
}


/* ===============================
   simpan produk
================================ */
document.getElementById("formTambah").addEventListener("submit", async (e)=>{
    e.preventDefault();

    const btn = document.getElementById("btnSubmit");
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = "Menyimpan...";

    try {
        const file = gambarInput.files[0];

        let imageUrl = "";

        /* upload dulu kalau ada gambar */
        if(file){
            imageUrl = await uploadGambar(file);
        }

        await db.collection("product").add({
            nama: nama.value,
            harga: Number(harga.value),
            gambar: imageUrl,
            deskripsi: deskripsi.value,
            created_at: firebase.firestore.FieldValue.serverTimestamp()
        });

        alert("Produk berhasil ditambahkan");
        location.href="/admin";
    } catch(error){
        console.error(error);
        alert("Gagal menyimpan produk: " + error.message);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});

</script>
@endpush
