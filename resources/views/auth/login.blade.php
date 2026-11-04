@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="auth-container">

    <div class="auth-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
    </div>

    <h2>Selamat Datang Kembali</h2>
    <p>Masuk menggunakan akun Google untuk melanjutkan</p>

    {{-- notif dari order --}}
    @if(request('from') == 'order')
        <p style="
            background: var(--danger-100);
            color: #b91c1c;
            padding:12px 14px;
            border-radius:10px;
            font-size:13.5px;
            margin: 18px 0 0;
            font-weight:600;
            text-align:left;
        ">
             Silahkan login terlebih dahulu untuk melakukan pemesanan
        </p>
    @endif


    <button class="google-btn" onclick="loginGoogle()">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg">
        Login dengan Google
    </button>

    <p class="auth-note">
        Belum punya akun?
        <a href="/register" style="color:var(--brand-600);font-weight:700;text-decoration:none;">
            Daftar Sekarang
        </a>
    </p>

</div>

@endsection



@push('scripts')
<script>

function loginGoogle() {

    const provider = new firebase.auth.GoogleAuthProvider();
    provider.setCustomParameters({ prompt: "select_account" });

    auth.signInWithPopup(provider)
        .then(async (result) => {

            const user = result.user;

            const ref  = db.collection("users").doc(user.uid);
            const snap = await ref.get();

            let role = "user";

            /* SIMPAN Tanda pengenal admin*/
            if (!snap.exists) {

                role = user.email === "suryamichaelwijayanto@gmail.com"
                    ? "admin"
                    : "user";

                await ref.set({
                    email: user.email,
                    role: role,
                    created_at: firebase.firestore.FieldValue.serverTimestamp()
                });

            } else {
                role = snap.data().role;
            }


            /* REDIRECT jika bukan admin*/
            const params = new URLSearchParams(window.location.search);

            if(params.get("from") === "order"){
                // balik lagi ke halaman order
                window.history.back();
                return;
            }

            location.href = role === "admin" ? "/admin" : "/";

        })
        .catch(err => alert(err.message));
}

</script>
@endpush
