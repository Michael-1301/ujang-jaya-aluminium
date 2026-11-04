<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') &middot; Admin | Ujang Jaya Aluminium</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:500,600,700,800|inter:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/admin.css">
    @stack('styles')
</head>
<body class="admin-body">

<div class="admin-shell">

    <!-- ================= SIDEBAR ================= -->
    <aside class="admin-sidebar" id="adminSidebar">

        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <span class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
                </svg>
            </span>
            <span class="brand-text">
                <strong>Ujang Jaya</strong>
                <small>Admin Panel</small>
            </span>
        </a>

        <nav class="sidebar-nav">
            <span class="nav-label">Menu</span>

            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.produk') }}" class="nav-item {{ request()->routeIs('admin.produk') || request()->is('admin/produk/edit/*') ? 'active' : '' }}">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                <span>Produk</span>
            </a>

            <a href="{{ route('admin.pesanan') }}" class="nav-item {{ request()->routeIs('admin.pesanan') || request()->is('admin/pesanan*') || request()->is('admin/orders*') ? 'active' : '' }}">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l3 3v17H6z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>
                <span>Pesanan</span>
            </a>

            <a href="{{ route('admin.pelanggan') }}" class="nav-item {{ request()->routeIs('admin.pelanggan') ? 'active' : '' }}">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Pelanggan</span>
            </a>

            <span class="nav-label">Lainnya</span>

            <a href="/" class="nav-item">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/></svg>
                <span>Lihat Toko</span>
            </a>

            <button type="button" class="nav-item nav-item-danger" onclick="logout()">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                <span>Logout</span>
            </button>
        </nav>

        <div class="sidebar-profile" id="sidebarProfile">
            <img id="sidebarAvatar" referrerpolicy="no-referrer">
            <div class="profile-meta">
                <strong id="sidebarName">Admin</strong>
                <span id="sidebarEmail">&nbsp;</span>
            </div>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- ================= MAIN ================= -->
    <div class="admin-main">

        <header class="admin-topbar">
            <button type="button" class="hamburger-btn" onclick="toggleSidebar()" aria-label="Buka menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>

            <div class="topbar-title">
                <h1>@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-subtitle')
                    <p>@yield('page-subtitle')</p>
                @endif
            </div>

            <div class="topbar-actions">
                @yield('page-actions')

                @unless(request()->routeIs('admin.dashboard'))
                    @include('partials.back-button', ['backFallback' => route('admin.dashboard')])
                @endunless
            </div>
        </header>

        <main class="admin-content">
            @yield('content')
        </main>
    </div>
</div>

@include('partials.firebase')

<script>
function toggleSidebar(){
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function logout(){
    auth.signOut().then(()=>{
        location.href = "/";
    });
}

/* =====================
   PROTEKSI ADMIN (berlaku untuk SEMUA halaman /admin/*)
===================== */
auth.onAuthStateChanged(async (user) => {

    if (!user) {
        location.href = "/login";
        return;
    }

    const snap = await db.collection("users").doc(user.uid).get();

    if (!snap.exists || snap.data().role !== "admin") {
        alert("Akses ditolak! Halaman ini khusus admin.");
        location.href = "/";
        return;
    }

    document.getElementById('sidebarAvatar').src =
        user.photoURL || ("https://ui-avatars.com/api/?background=2563eb&color=fff&name=" + encodeURIComponent(user.displayName || user.email || "Admin"));
    document.getElementById('sidebarName').textContent = user.displayName || "Admin";
    document.getElementById('sidebarEmail').textContent = user.email || "";
});
</script>

@stack('scripts')

</body>
</html>
