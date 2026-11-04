@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')
@section('page-subtitle', 'Perbarui detail produk aluminium')

@section('content')

<div class="admin-page">
    <div class="admin-card">

        <h2>Detail Produk</h2>

        <form class="admin-form" id="formEdit">

            <label>Nama Produk</label>
            <input type="text" id="nama" required>

            <label>Harga</label>
            <input type="number" id="harga" required>

            <label>Ganti Gambar (opsional)</label>
            <input type="file" id="gambarInput" accept="image/*">

            <!-- preview -->
            <img id="previewImg"
                 style="margin-top:10px;max-width:200px;border-radius:10px;box-shadow:var(--shadow-sm);">

            <label>Deskripsi</label>
            <textarea id="deskripsi"></textarea>

            <div class="form-actions">
                <button type="submit" class="btn-save" id="btnSubmit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Update
                </button>
                <a href="/admin" class="btn-cancel">Batal</a>
            </div>

        </form>

    </div>
</div>

@endsection


@push('scripts')
<script>
const id  = "{{ $id }}";
const ref = db.collection("product").doc(id);

let oldImageUrl = "";


/* ===============================
   ambil data lama
================================ */
ref.get().then(doc=>{
    const p = doc.data();

    nama.value       = p.nama;
    harga.value      = p.harga;
    deskripsi.value  = p.deskripsi;

    oldImageUrl      = p.gambar || "";
    previewImg.src   = oldImageUrl || "";
});


/* ===============================
   preview gambar
================================ */
gambarInput.addEventListener("change",(e)=>{
    const file = e.target.files[0];

    if(file){
        previewImg.src = URL.createObjectURL(file);
    }
});


/* ===============================
   upload cloudinary (sama kayak tambah)
================================ */
async function uploadGambar(file){

    const formData = new FormData();
    formData.append("image", file);

    const res = await fetch("/upload-image", {
        method:"POST",
        body:formData,
        headers:{
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    });

    const data = await res.json();
    return data.url;
}


/* ===============================
   UPDATE PRODUK
================================ */
document.getElementById("formEdit").addEventListener("submit", async (e)=>{
    e.preventDefault();

    const btn = document.getElementById("btnSubmit");
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = "Menyimpan...";

    try {
        const file = gambarInput.files[0];

        let imageUrl = oldImageUrl;

        // kalau pilih gambar baru → upload
        if(file){
            imageUrl = await uploadGambar(file);
        }

        await ref.update({
            nama: nama.value,
            harga: Number(harga.value),
            gambar: imageUrl,
            deskripsi: deskripsi.value
        });

        alert("Produk berhasil diupdate");
        location.href="/admin";
    } catch(error){
        console.error(error);
        alert("Gagal mengupdate produk: " + error.message);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});
</script>
@endpush
