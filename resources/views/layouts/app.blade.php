<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &middot; Ujang Jaya Aluminium</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:500,600,700,800|inter:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="/css/style.css">
    @stack('styles')
</head>
<body>

<!-- ================= TOPBAR ================= -->
<div class="topbar">
    <a href="/" class="brand">
        <span class="brand-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
            </svg>
        </span>
        <span class="brand-text">
            <strong>Ujang Jaya</strong>
            <small>Aluminium</small>
        </span>
    </a>

    <!-- BELUM LOGIN -->
    <div class="auth-menu" id="authMenu">
        <a href="/login" class="login-text">Login</a>
        <a href="/register" class="auth-btn">Daftar</a>
    </div>

    <!-- SUDAH LOGIN -->
    <div class="profile-wrapper" id="profileMenu" style="display:none;">
        <div class="profile-btn" onclick="toggleProfile()">
            <img id="profilePhoto" referrerpolicy="no-referrer">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </div>

        <div class="profile-dropdown" id="profileDropdown">

            <!-- ================= ADMIN ONLY ================= -->
            <div id="adminMenus" style="display:none;">
                <a href="{{ route('admin.dashboard') }}">Dashboard Admin</a>
                <a href="{{ route('admin.produk') }}">Tambah Produk</a>
                <a href="/admin/pesanan">Daftar Pesanan</a>
                <hr>
            </div>

            <!-- ================= USER ONLY ================= -->
            <div id="userMenus" style="display:none;">
                <a href="/pesanan-saya">Pesanan Saya</a>
                <hr>
            </div>

            <!-- SEMUA ROLE -->
            <a href="#" onclick="logout()" class="logout-link">Logout</a>

        </div>
    </div>
</div>


<div class="container">

    @unless(request()->is('/'))
        <div class="back-button-row">
            @include('partials.back-button', ['backFallback' => '/'])
        </div>
    @endunless

    @yield('content')
</div>


@include('partials.firebase')

<script>
const authMenu    = document.getElementById("authMenu");
const profileMenu = document.getElementById("profileMenu");
const profileImg  = document.getElementById("profilePhoto");

const adminMenus  = document.getElementById("adminMenus");
const userMenus   = document.getElementById("userMenus");


/* ================= AUTH STATE ================= */
auth.onAuthStateChanged(async (user) => {

    if (!user) {
        authMenu.style.display = "flex";
        profileMenu.style.display = "none";
        return;
    }

    authMenu.style.display = "none";
    profileMenu.style.display = "block";

    profileImg.src = user.photoURL || "https://ui-avatars.com/api/?name=User";

    // ambil role dari firestore
    const snap = await db.collection("users").doc(user.uid).get();
    const role = snap.exists ? snap.data().role : "user";

    /* ===== ROLE CONTROL ===== */
    if(role === "admin"){
        adminMenus.style.display = "block";
        userMenus.style.display  = "none";
    } else {
        adminMenus.style.display = "none";
        userMenus.style.display  = "block";
    }
});


/* ================= DROPDOWN ================= */
function toggleProfile(){
    document.getElementById("profileDropdown").classList.toggle("show");
}

document.addEventListener("click",(e)=>{
    if(!e.target.closest(".profile-wrapper")){
        document.getElementById("profileDropdown").classList.remove("show");
    }
});


/* ================= LOGOUT ================= */
function logout(){
    auth.signOut().then(()=>{
        location.href="/";
    });
}
</script>

@stack('scripts')

</body>
</html>
