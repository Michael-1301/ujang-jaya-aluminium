@extends('layouts.app')

@section('title', 'Daftar')

@section('content')

<div class="auth-container">

    <div class="auth-icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>

    <h2>Buat Akun Baru</h2>
    <p>Daftar dengan akun Google untuk mulai berbelanja</p>

    <button class="google-btn" onclick="registerGoogle()">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg">
        Daftar dengan Google
    </button>

    <p class="auth-note">
        Sudah punya akun?
        <a href="/login" style="color:var(--brand-600);font-weight:700;text-decoration:none;">
            Login di sini
        </a>
    </p>

</div>

@endsection


@push('scripts')
<script>

function registerGoogle() {

    const provider = new firebase.auth.GoogleAuthProvider();
    provider.setCustomParameters({ prompt: "select_account" });

    auth.signInWithPopup(provider)
        .then(async (result) => {

            const user = result.user;

            const ref  = db.collection("users").doc(user.uid);
            const snap = await ref.get();

            let role = "user";

            if (!snap.exists) {

                role = user.email === "johanadisaputra07@gmail.com"
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

            location.href = role === "admin" ? "/admin" : "/";

        })
        .catch(err => alert(err.message));
}

</script>
@endpush
